<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Source\ChildrenCategoriesBlock;

/**
 * Class DisplayMode
 * @package Amasty\Shopby\Model\Source\ChildrenCategoriesBlock
 */
class DisplayMode
{
    const DISABLED = 0;
    const IMAGES = 1;
    const LABELS = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $optionValue => $optionLabel) {
            $options[] = [
                'value' => $optionValue,
                'label' => $optionLabel
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::DISABLED => __('Disabled'),
            self::IMAGES => __('Category Thumbnail Images'),
            self::LABELS => __('Category Names Without Images')
        ];
    }
}
