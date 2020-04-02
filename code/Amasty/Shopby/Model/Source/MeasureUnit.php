<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Source;

/**
 * Class MeasureUnit
 * @package Amasty\Shopby\Model\Source
 */
class MeasureUnit implements \Magento\Framework\Option\ArrayInterface
{
    const CUSTOM            = 0;
    const CURRENCY_SYMBOL   = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::CURRENCY_SYMBOL,
                'label' => __('Store Currency')
            ],
            [
                'value' => self::CUSTOM,
                'label' => __('Custom label')
            ]
        ];
    }
}
