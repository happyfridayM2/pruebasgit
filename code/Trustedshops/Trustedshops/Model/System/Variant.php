<?php
/**
 * @category  Trustedshops
 * @package   Trustedshops\Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */

namespace Trustedshops\Trustedshops\Model\System;

use Magento\Framework\Option\ArrayInterface;

class Variant implements ArrayInterface
{
    const VARIANT_HIDE = 'hide';
    const VARIANT_REVIEWS = 'reviews';
    const VARIANT_NO_REVIEWS = 'default';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::VARIANT_REVIEWS, 'label' => __('Display Trustbadge with review stars')],
            ['value' => self::VARIANT_NO_REVIEWS, 'label' => __('Display Trustbadge without review stars')],
            ['value' => self::VARIANT_HIDE, 'label' => __("Don't show Trustbadge")],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::VARIANT_REVIEWS => __('Display Trustbadge with review stars'),
            self::VARIANT_NO_REVIEWS => __('Display Trustbadge without review stars'),
            self::VARIANT_HIDE => __("Don't show Trustbadge"),
        ];
    }
}
