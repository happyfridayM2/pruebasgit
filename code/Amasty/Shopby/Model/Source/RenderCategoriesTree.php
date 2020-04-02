<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class RenderCategoriesTree
 * @package Amasty\Shopby\Model\Source
 */
class RenderCategoriesTree implements ArrayInterface
{
    const NO = 0;
    const YES = 1;

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::NO,
                'label' => __('No')
            ],
            [
                'value' => self::YES,
                'label' => __('Yes')
            ],
        ];
    }
}
