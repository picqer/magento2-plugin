<?php

namespace Picqer\Integration\Api\Data;

interface PushOrderMetadataInterface
{
    const STATUS_PROCESSED = 1;
    const STATUS_UNPROCESSED = 0;

    const COLUMN_ID = 'id';
    const COLUMN_ORDER_ID = 'order_id';
    const COLUMN_ORDER_STATUS = 'order_status';
    const COLUMN_MESSAGE = 'message';
    const COLUMN_STATUS = 'status';
    const COLUMN_CREATED_AT = 'created_at';

    public function getId();

    public function getOrderId();

    public function getCreatedAt();

    public function setOrderId($orderId);

    public function setOrderStatus($orderStatus);

    public function setStatus($status);

    public function setMessage($message);

}
