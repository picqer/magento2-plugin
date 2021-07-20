<?php

namespace Picqer\Integration\Observer;

use Magento\Framework\Event\ObserverInterface;

class SendWebhook implements ObserverInterface
{
    protected $_scopeConfig;
    protected $_curl;
    protected $_logger;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_curl = $curl;
        $this->_logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $active = $this->_scopeConfig->getValue('picqer_integration_options/webhook_settings/active');
        if ((int)$active !== 1) {
            return;
        }

        $subDomain = $this->_scopeConfig->getValue('picqer_integration_options/webhook_settings/picqer_subdomain');
        $magentoKey = $this->_scopeConfig->getValue('picqer_integration_options/webhook_settings/connection_key');

        if (empty($subDomain) || empty($magentoKey)) {
            return; // Not fully configured
        }

        $order = $observer->getEvent()->getOrder();

        $orderData = [];
        $orderData['increment_id'] = $order->getIncrementId();
        $orderData['picqer_magento_key'] = $magentoKey;

        $this->_curl->addHeader("Content-Type", "application/json");
        $this->_curl->setTimeout(2); // in seconds
        try {
            $this->_curl->post('https://' . trim($subDomain) . '.picqer.com/webshops/magento2/orderPush/' . trim($magentoKey), json_encode($orderData));
        } catch (\Exception $e) {
            $this->_logger->debug('Exception occurred with Picqer: ' . $e->getMessage());
        }
    }
}
