<?php

namespace Picqer\Webhooks\Model\Sales;

class Orderstate implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 'new', 'label' => 'New'],
            ['value' => 'pending_payment', 'label' => 'Pending Payment'],
            ['value' => 'processing', 'label' => 'Processing'],
            ['value' => 'complete', 'label' => 'Complete'],
            ['value' => 'closed', 'label' => 'Closed'],
            ['value' => 'canceled', 'label' => 'Canceled'],
            ['value' => 'holded', 'label' => 'On Hold'],
            ['value' => 'payment_review', 'label' => 'Payment Review'],
        ];
    }
}