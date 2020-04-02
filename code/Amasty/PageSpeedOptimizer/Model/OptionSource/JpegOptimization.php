<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\OptionSource;

use Magento\Framework\Option\ArrayInterface;

class JpegOptimization implements ArrayInterface
{
    const DO_NOT_OPTIMIZE = 0;
    const JPEGOPTIM = 1;
    const JPEGOPTIM_90 = 998;
    const JPEGOPTIM_80 = 999;
    /*
    const NEW_OPTIMIZATION_TOOL = 2;
    */

    const TOOLS = [
        self::JPEGOPTIM => [
            'name' =>  'jpegoptim 100% quality',
            'command' => 'jpegoptim --all-progressive --strip-xmp --strip-com --strip-exif --strip-iptc %s',
            'check' => [
                'command' => 'jpegoptim --help',
                'result' => 'Usage: jpegoptim'
            ]
        ],
        self::JPEGOPTIM_90 => [
            'name' =>  'jpegoptim 90% quality',
            'command' => 'jpegoptim --all-progressive --strip-all -m 90 %s',
            'check' => [
                'command' => 'jpegoptim --help',
                'result' => 'Usage: jpegoptim'
            ]
        ],
        self::JPEGOPTIM_80 => [
            'name' =>  'jpegoptim 80% quality',
            'command' => 'jpegoptim --all-progressive --strip-all -m 80 %s',
            'check' => [
                'command' => 'jpegoptim --help',
                'result' => 'Usage: jpegoptim'
            ]
        ],
        /*
        self::NEW_OPTIMIZATION_TOOL => [
            'name' =>  'Label of Optimization Tool',
            'command' => 'command for Optimization Tool. %s is placeholder for filename',
            'check' => [
                'command' => 'jpegoptim --help',
                'result' => 'Expected Result'
            ]
        ],
        */
    ];

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
        $result = [
            self::DO_NOT_OPTIMIZE => __('Do not Optimize'),
        ];
        foreach (self::TOOLS as $toolId => $tool) {
            $result[$toolId] = $tool['name'];
        }

        return $result;
    }
}
