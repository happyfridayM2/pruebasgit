<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Model\Source;

class ConfirmPopup implements \Magento\Framework\Option\ArrayInterface
{
    const MINI_PAGE = '0';
    const OPTIONS = '1';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::MINI_PAGE,
                'label' => __('Mini Product Page')
            ],
            [
                'value' => self::OPTIONS,
                'label' => __('Custom Options & Product Qty')
            ]
        ];

        return $options;
    }
}
