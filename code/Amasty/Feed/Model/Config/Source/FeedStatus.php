<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class FeedStatus implements ArrayInterface
{
    /**#@+
     * Feed status
     */
    const FAILED = 3;
    const GENERATE_NEXT_CRON = 4;
    const PROCESSING = 2;
    const READY = 1;
    const NOT_GENERATED = 0;
    /**#@-*/

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
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
            self::FAILED => __('Failed'),
            self::PROCESSING => __('Processing'),
            self::READY => __('Ready'),
            self::NOT_GENERATED => __('Not yet Generated'),
            self::GENERATE_NEXT_CRON => __('Will be generated on next cron run')
        ];
    }
}
