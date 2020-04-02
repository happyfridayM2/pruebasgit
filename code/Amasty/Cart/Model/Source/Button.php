<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Amasty\Cart\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Button implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => '0',
                'label' => __('Stay on Current Page')
            ],
            [
                'value' => '1',
                'label' => __('Go to Category Page')
            ]
        ];

        return $options;
    }
}
