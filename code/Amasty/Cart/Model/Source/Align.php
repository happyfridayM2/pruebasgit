<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Align implements ArrayInterface
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
                'label' =>__('Center')
            ],
            [
                'value' => '1',
                'label' =>__('Top')
            ],
            [
                'value' => '2',
                'label' =>__('Top Left')
            ],
            [
                'value' => '3',
                'label' =>__('Top Right')
            ],
            [
                'value' => '4',
                'label' =>__('Left')
            ],
            [
                'value' => '5',
                'label' =>__('Right')
            ]
        ];

        return $options;
    }
}
