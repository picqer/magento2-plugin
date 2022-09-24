<?php

namespace Picqer\Integration\Cron;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Picqer\Integration\Api\PushOrderMetadataRepositoryInterface;
use Psr\Log\LoggerInterface;

class PushOrder
{
    const XML_PATH_ENABLED = 'picqer_integration_options/webhook_settings/cron_enabled';
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var PushOrderMetadataRepositoryInterface
     */
    private $pushOrderMetadataRepository;
    /**
     * @var Curl
     */
    private $_curl;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        LoggerInterface $logger,
        PushOrderMetadataRepositoryInterface $pushOrderMetadataRepository,
        OrderRepositoryInterface $orderRepository,
        Curl $_curl,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->pushOrderMetadataRepository = $pushOrderMetadataRepository;
        $this->_curl = $_curl;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        if (!$this->scopeConfig->getValue(self::XML_PATH_ENABLED,ScopeInterface::SCOPE_STORE)) {
            return $this;
        }
        $timeToCompare = date("Y-m-d H:i:s", strtotime('-1 days'));
        foreach ($this->pushOrderMetadataRepository->getUnprocessedList() as $pushOrderMetadata) {
            try {
                if ($pushOrderMetadata->getCreatedAt() < $timeToCompare) {
                    throw new Exception(sprintf("Too late for push: %s < %s", $pushOrderMetadata->getCreatedAt(), $timeToCompare));
                }
                $message = $this->pushOrder($pushOrderMetadata->getOrderId());
            } catch (Exception $e) {
                $this->logger->error("Picqer push order error: " . $e->getMessage());
                $message = $e->getMessage();
            }
            $this->pushOrderMetadataRepository->setProcessed($pushOrderMetadata, $message);
        }

        return $this;
    }

    private function pushOrder($orderId)
    {
        $order = $this->orderRepository->get((int)$orderId);

        $subDomain = $this->scopeConfig->getValue('picqer_integration_options/webhook_settings/picqer_subdomain');
        $magentoKey = $this->scopeConfig->getValue('picqer_integration_options/webhook_settings/connection_key');

        if (empty($subDomain) || empty($magentoKey)) {
            throw new Exception("Module is not fully configured");
        }

        $orderData = [];
        $orderData['increment_id'] = $order->getIncrementId();
        $orderData['picqer_magento_key'] = $magentoKey;

        $this->_curl->addHeader("Content-Type", "application/json");
        $this->_curl->setTimeout(2); // in seconds
        $this->_curl->post(sprintf('https://%s.picqer.com/webshops/magento2/orderPush/%s', trim($subDomain), trim($magentoKey)), json_encode($orderData));

        return $this->_curl->getBody();
    }

}
