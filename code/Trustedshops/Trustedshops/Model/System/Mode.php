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

class Mode implements ArrayInterface
{
    const MODE_STANDARD = 'standard';
    const MODE_EXPERT = 'expert';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::MODE_STANDARD, 'label' => __('Standard')],
            ['value' => self::MODE_EXPERT, 'label' => __('Expert')],
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
            self::MODE_STANDARD => __('Standard'),
            self::MODE_EXPERT => __('Expert'),
        ];
    }
}
