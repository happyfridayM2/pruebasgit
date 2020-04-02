<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Config\Source;

use Amasty\Feed\Model\Feed;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Compress
 */
class Compress implements ArrayInterface
{
    /**#@+
     * Compressing types
     */
    const COMPRESS_NONE = '';
    const COMPRESS_ZIP = 'zip';
    const COMPRESS_GZ = 'gz';
    const COMPRESS_BZ = 'bz2';
    /**#@-*/

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
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
        $options = [
            self::COMPRESS_NONE => __('None'),
            self::COMPRESS_ZIP => __('Zip'),
            self::COMPRESS_GZ => __('Gz'),
            self::COMPRESS_BZ => __('Bz')
        ];

        return $options;
    }
}
