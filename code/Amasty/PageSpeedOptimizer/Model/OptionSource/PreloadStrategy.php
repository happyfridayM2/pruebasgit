<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\OptionSource;

use Magento\Framework\Option\ArrayInterface;

class PreloadStrategy implements ArrayInterface
{
    const WEBP_RESOLUTIONS = 0;
    const SKIP_IMAGES = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $widgetType => $label) {
            $optionArray[] = ['value' => $widgetType, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::WEBP_RESOLUTIONS => __('WebP Format + Image Resolutions'),
            self::SKIP_IMAGES => __('Original Format of Images'),
        ];
    }
}
