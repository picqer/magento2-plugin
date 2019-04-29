<?php

namespace Picqer\Magento2\Observer;

use Magento\Framework\Event\ObserverInterface;

class sendWebhook implements ObserverInterface
{
    protected $_scopeConfig;
    protected $_curl;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_curl = $curl;
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
        $this->_curl->post('https://' . $subDomain . '.picqer.com/webshops/magento2/orderPush/' . $magentoKey, json_encode($orderData));
    }
}