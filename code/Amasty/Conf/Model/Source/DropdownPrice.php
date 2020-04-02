<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
namespace Amasty\Conf\Model\Source;

class DropdownPrice implements \Magento\Framework\Option\ArrayInterface
{
    const NO_PRICE = 0;
    const PRICE_DIFFERENCE = 1;
    const ACTUAL_PRICE = 2;
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::NO_PRICE,
                'label' => __('Don`t Show Price')
            ],
            [
                'value' => self::PRICE_DIFFERENCE,
                'label' => __('Show Price Difference')
            ],
            [
                'value' => self::ACTUAL_PRICE,
                'label' => __('Show Actual Price')
            ]
        ];

        return $options;
    }
}
