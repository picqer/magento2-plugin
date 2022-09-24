<?php

namespace Picqer\Integration\Model;

use Magento\Framework\Exception\LocalizedException;
use Picqer\Integration\Api\PushOrderMetadataRepositoryInterface;
use Picqer\Integration\Api\Data\PushOrderMetadataInterface;
use Picqer\Integration\Model\ResourceModel\PushOrderMetadata as ResourceModel;
use Picqer\Integration\Model\PushOrderMetadataFactory as PushOrderMetadataFactory;
use Picqer\Integration\Model\ResourceModel\PushOrderMetadata\CollectionFactory as CollectionFactory;
use Picqer\Integration\Model\ResourceModel\PushOrderMetadata\Collection;

class PushOrderMetadataRepository implements PushOrderMetadataRepositoryInterface
{
    /**
     * @var ResourceModel
     */
    private $resourceModel;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var PushOrderMetadataFactory
     */
    private $pushOrderMetadataFactory;

    public function __construct(
        ResourceModel $resourceModel,
        PushOrderMetadataFactory $pushOrderMetadataFactory,
        CollectionFactory $collectionFactory
    )
    {
        $this->resourceModel = $resourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->pushOrderMetadataFactory = $pushOrderMetadataFactory;
    }

    public function setProcessed(PushOrderMetadataInterface $pushOrderMetadata, $message)
    {
        $pushOrderMetadata->setMessage((string)$message);
        $pushOrderMetadata->setStatus(PushOrderMetadataInterface::STATUS_PROCESSED);
        return $this->save($pushOrderMetadata);
    }

    /**
     * @param $id
     * @return PushOrderMetadataInterface
     * @throws LocalizedException
     */
    public function getById($id)
    {
        $model = $this->pushOrderMetadataFactory->create();
        $this->resourceModel->load($model, (int)$id, PushOrderMetadataInterface::COLUMN_ID);
        if (!$model->getId()) {
            throw new LocalizedException(__('Unable to find item'));
        }
        return $model;
    }

    function getUnprocessedList()
    {
        $list = [];
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(PushOrderMetadataInterface::COLUMN_STATUS, ['eq' => PushOrderMetadataInterface::STATUS_UNPROCESSED]);
        foreach ($collection as $item) {
            $model = $this->getById((int)$item->getId());
            $list[$model->getId()] = $model;
        }
        return $list;
    }

    function save(PushOrderMetadataInterface $pushOrderMetadata)
    {
        $this->resourceModel->save($pushOrderMetadata);
        return $this->getById((int)$pushOrderMetadata->getId());
    }
}
