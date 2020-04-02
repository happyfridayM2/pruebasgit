<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class DisplayElements implements ArrayInterface
{
    const IMAGE = 'image';
    const QTY = 'qty';
    const COUNT = 'count';
    const SUBTOTAL = 'subtotal';
    const CHECKOUT_BUTTON = 'checkout_button';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::IMAGE,
                'label' =>__('Product Image')
            ],
            [
                'value' => self::QTY,
                'label' =>__('Product Quantity Field')
            ],
            [
                'value' => self::COUNT,
                'label' =>__('Number of Products in Cart')
            ],
            [
                'value' => self::SUBTOTAL,
                'label' =>__('Cart Subtotal')
            ],
            [
                'value' => self::CHECKOUT_BUTTON,
                'label' =>__('Go to Checkout Button')
            ],
        ];

        return $options;
    }
}
