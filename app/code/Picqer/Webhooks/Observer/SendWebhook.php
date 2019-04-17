<?php

namespace Picqer\Webhooks\Observer;

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
        $active = $this->_scopeConfig->getValue('picqer_webhook_options/general_settings/active');
        if ($active != 1) {
            return;
        }

        if (! $this->hasCorrectState($observer->getEvent()->getOrder()->getStatus())) {
            return;
        }

        $magentoKey = $this->_scopeConfig->getValue('picqer_webhook_options/general_settings/connection_key');
        $domain = $this->_scopeConfig->getValue('picqer_webhook_options/general_settings/picqer_domain');

        $order = $observer->getEvent()->getOrder();

        $orderData = [];
        $orderData['increment_id'] = $order->getIncrementId();
        $orderData['picqer_magento_key'] = $magentoKey;
        $orderData = json_encode($orderData);

        $this->_curl->post('https://' . $domain . '.picqer.com/webshops/magento2/orderPush/' . $magentoKey, $orderData);
    }

    private function hasCorrectState($status)
    {
        $configStatus = $this->_scopeConfig->getValue('picqer_webhook_options/general_settings/order_state');
        return $status === $configStatus;
    }
}
