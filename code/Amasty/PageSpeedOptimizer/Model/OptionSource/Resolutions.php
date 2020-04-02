<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\OptionSource;

use Magento\Framework\Option\ArrayInterface;

class Resolutions implements ArrayInterface
{
    const MOBILE = 1;
    const TABLET = 2;

    const RESOLUTIONS = [
        self::TABLET => [
            'dir' => 'amasty' . DIRECTORY_SEPARATOR .  'amopttablet' . DIRECTORY_SEPARATOR,
            'path' => 'tablet_path',
            'width' => 768,
            'min-width' => 480
        ],
        self::MOBILE => [
            'dir' => 'amasty' . DIRECTORY_SEPARATOR .  'amoptmobile' . DIRECTORY_SEPARATOR,
            'path' => 'mobile_path',
            'width' => 480
        ]
    ];

    const WEBP_DIR = 'amasty' . DIRECTORY_SEPARATOR .  'webp' . DIRECTORY_SEPARATOR;

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
            self::MOBILE => __('Mobile'),
            self::TABLET => __('Tablet'),
        ];
    }
}
