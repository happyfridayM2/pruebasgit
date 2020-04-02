<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Source;

/**
 * Class PositionLabel
 * @package Amasty\Shopby\Model\Source
 */
class PositionLabel implements \Magento\Framework\Option\ArrayInterface
{
    const POSITION_BEFORE = 0;
    const POSITION_AFTER = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::POSITION_BEFORE,
                'label' => __('Before')
            ],
            [
                'value' => self::POSITION_AFTER,
                'label' => __('After')
            ]
        ];
    }
}
