<?php

namespace Picqer\Integration\Model;

use Magento\Framework\Model\AbstractModel;
use Picqer\Integration\Api\Data\PushOrderMetadataInterface;
use Picqer\Integration\Model\ResourceModel\PushOrderMetadata as Model;

class PushOrderMetadata extends AbstractModel implements PushOrderMetadataInterface
{
    protected function _construct()
    {
        $this->_init(Model::class);
    }

    public function getId()
    {
        return $this->getData(PushOrderMetadataInterface::COLUMN_ID);
    }

    public function getOrderId()
    {
        return $this->getData(PushOrderMetadataInterface::COLUMN_ORDER_ID);
    }

    public function getCreatedAt()
    {
        return $this->getData(PushOrderMetadataInterface::COLUMN_CREATED_AT);
    }

    public function setOrderId($orderId)
    {
        $this->setData(PushOrderMetadataInterface::COLUMN_ORDER_ID, (int)$orderId);
        return $this;
    }

    public function setOrderStatus($orderStatus)
    {
        $this->setData(PushOrderMetadataInterface::COLUMN_ORDER_STATUS, (string)$orderStatus);
        return $this;
    }

    public function setStatus($status)
    {
        $this->setData(PushOrderMetadataInterface::COLUMN_STATUS, (int)$status);
        return $this;
    }

    public function setMessage($message)
    {
        $this->setData(PushOrderMetadataInterface::COLUMN_MESSAGE, (string)$message);
        return $this;
    }

}
