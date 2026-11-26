<?php
declare(strict_types=1);

namespace Picqer\Integration\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class SendWebhook implements ObserverInterface
{
    public const XML_PATH_ACTIVE = 'picqer_integration_options/webhook_settings/active';
    public const XML_PATH_SUBDOMAIN = 'picqer_integration_options/webhook_settings/picqer_subdomain';
    public const XML_PATH_KEY = 'picqer_integration_options/webhook_settings/connection_key';

    protected $_scopeConfig;
    protected $_curlFactory;
    protected $_logger;
    protected $_encryptor;

    /** @var array<string, bool> Orders already pushed during this request */
    private $pushed = [];

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CurlFactory $curlFactory,
        LoggerInterface $logger,
        EncryptorInterface $encryptor
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_curlFactory = $curlFactory;
        $this->_logger = $logger;
        $this->_encryptor = $encryptor;
    }

    /**
     * Pushes the order to Picqer after it has been committed. Orders are pushed once per request.
     */
    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        if ($order === null) {
            return;
        }

        $incrementId = (string)$order->getIncrementId();
        if (isset($this->pushed[$incrementId])) {
            return;
        }

        $storeId = $order->getStoreId();

        if ((int)$this->_scopeConfig->getValue(self::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORE, $storeId) !== 1) {
            return;
        }

        $subDomain = trim((string)$this->_scopeConfig->getValue(
            self::XML_PATH_SUBDOMAIN,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
        $magentoKey = $this->getConnectionKey($storeId);

        if ($subDomain === '' && $magentoKey === '') {
            return; // Not configured for this store
        }

        // A subdomain with dots, slashes or other characters could send the key to another host
        if ($magentoKey === '' || !preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\z/i', $subDomain)) {
            $this->_logger->warning(sprintf(
                'Picqer order push skipped for order %s: invalid subdomain or empty/undecryptable connection key',
                $incrementId
            ));
            return;
        }
        $this->pushed[$incrementId] = true;

        $payload = json_encode([
            'increment_id' => $incrementId,
            'picqer_magento_key' => $magentoKey,
        ]);
        if ($payload === false) {
            return;
        }

        // A fresh client: the shared Curl instance keeps options, headers and cookies of other callers
        $curl = $this->_curlFactory->create();
        $curl->setHeaders(['Content-Type' => 'application/json']);
        $curl->setOptions([
            CURLOPT_CONNECTTIMEOUT => 2, // in seconds
            CURLOPT_TIMEOUT => 2, // in seconds
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        // Never log the URL or key: the connection key is part of the URL
        try {
            $curl->post(
                sprintf('https://%s.picqer.com/webshops/magento2/orderPush/%s', $subDomain, rawurlencode($magentoKey)),
                $payload
            );
            $status = (int)$curl->getStatus();
            if ($status < 200 || $status >= 300) {
                $this->_logger->warning(sprintf(
                    'Picqer order push for order %s returned HTTP status %d',
                    $incrementId,
                    $status
                ));
            }
        } catch (\Throwable $e) {
            $this->_logger->warning(sprintf(
                'Picqer order push for order %s failed: %s',
                $incrementId,
                $e->getMessage()
            ));
        }
    }

    /**
     * Returns the decrypted connection key. Values saved before the field was encrypted
     * (plain text, no "<keyversion>:<type>:" prefix) are returned as is.
     *
     * @param int|string|null $storeId
     */
    private function getConnectionKey($storeId): string
    {
        $value = trim((string)$this->_scopeConfig->getValue(self::XML_PATH_KEY, ScopeInterface::SCOPE_STORE, $storeId));
        if ($value === '') {
            return '';
        }
        if (preg_match('/^\d+:\d+:/', $value)) {
            return trim($this->_encryptor->decrypt($value));
        }

        return $value;
    }
}
