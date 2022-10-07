<?php

namespace Picqer\Integration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PushOrderMetadata extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('picqer_push_order_metadata', 'id');
    }

}
