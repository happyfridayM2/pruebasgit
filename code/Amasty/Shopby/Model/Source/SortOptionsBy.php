<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Source;

/**
 * Class SortOptionsBy
 * @package Amasty\Shopby\Model\Source
 */
class SortOptionsBy implements \Magento\Framework\Option\ArrayInterface
{
    const POSITION = 0;
    const NAME = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::POSITION,
                'label' => __('Position')
            ],
            [
                'value' => self::NAME,
                'label' => __('Name')
            ],
        ];
    }
}
