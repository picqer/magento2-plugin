<?php
declare(strict_types=1);

namespace Picqer\Integration\Model\Sales;

use Magento\Framework\Data\OptionSourceInterface;

class Active implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 1, 'label' => __('Yes')],
            ['value' => 0, 'label' => __('No')]
        ];
    }
}
