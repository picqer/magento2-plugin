<?php

namespace Picqer\Integration\Model\ResourceModel\PushOrderMetadata;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Picqer\Integration\Model\PushOrderMetadata as Model;
use Picqer\Integration\Model\ResourceModel\PushOrderMetadata as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}



