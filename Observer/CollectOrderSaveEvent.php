<?php

namespace Picqer\Integration\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Picqer\Integration\Api\PushOrderMetadataRepositoryInterface;
use Picqer\Integration\Cron\PushOrder;
use Picqer\Integration\Api\Data\PushOrderMetadataInterface;
use Picqer\Integration\Api\Data\PushOrderMetadataInterfaceFactory;
use Psr\Log\LoggerInterface;

class CollectOrderSaveEvent implements ObserverInterface
{

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var PushOrderMetadataInterfaceFactory
     */
    private $pushOrderMetadataInterfaceFactory;
    /**
     * @var PushOrderMetadataRepositoryInterface
     */
    private $pushOrderMetadataRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        PushOrderMetadataInterfaceFactory $pushOrderMetadataInterfaceFactory,
        PushOrderMetadataRepositoryInterface $pushOrderMetadataRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->pushOrderMetadataInterfaceFactory = $pushOrderMetadataInterfaceFactory;
        $this->pushOrderMetadataRepository = $pushOrderMetadataRepository;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        if (!$this->scopeConfig->isSetFlag(PushOrder::XML_PATH_ENABLED)) {
            return;
        }

        try {
            $order = $observer->getEvent()->getOrder();
            /** @var PushOrderMetadataInterface $pushOrderMetadata */
            $pushOrderMetadata = $this->pushOrderMetadataInterfaceFactory->create();
            $pushOrderMetadata->setOrderId($order->getId());
            $pushOrderMetadata->setOrderStatus($order->getStatus());
            $this->pushOrderMetadataRepository->save($pushOrderMetadata);
        } catch (\Exception $e) {
            $this->logger->error("Can not create PushOrderMetadata instance: " . $e->getMessage());
        }

    }
}
