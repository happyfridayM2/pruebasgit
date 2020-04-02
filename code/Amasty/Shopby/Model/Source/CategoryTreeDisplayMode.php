<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Source;

/**
 * Class CategoryTreeDisplayMode
 * @package Amasty\Shopby\Model\Source
 */
class CategoryTreeDisplayMode implements \Magento\Framework\Option\ArrayInterface
{
    const SHOW_LABELS_ONLY = 0;
    const SHOW_IMAGES_ONLY = 1;
    const SHOW_LABELS_IMAGES = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::SHOW_LABELS_ONLY,
                'label' => __('Show Labels Only')
            ],
            [
                'value' => self::SHOW_IMAGES_ONLY,
                'label' => __('Show Images Only')
            ],
            [
                'value' => self::SHOW_LABELS_IMAGES,
                'label' => __('Show Labels And Images')
            ],
        ];
    }
}
