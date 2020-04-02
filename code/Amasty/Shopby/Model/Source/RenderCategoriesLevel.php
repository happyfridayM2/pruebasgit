<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Source;

/**
 * Class RenderCategoriesLevel
 * @package Amasty\Shopby\Model\Source
 */
class RenderCategoriesLevel implements \Magento\Framework\Option\ArrayInterface
{
    const ROOT_CATEGORY = 1;
    const CURRENT_CATEGORY_LEVEL = 2;
    const CURRENT_CATEGORY_CHILDREN = 3;

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ROOT_CATEGORY,
                'label' => __('Root Category')
            ],
            [
                'value' => self::CURRENT_CATEGORY_LEVEL,
                'label' => __('Current Category Level')
            ],
            [
                'value' => self::CURRENT_CATEGORY_CHILDREN,
                'label' => __('Current Category Children')
            ],
        ];
    }
}
