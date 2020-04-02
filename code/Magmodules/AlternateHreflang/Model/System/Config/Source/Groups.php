<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Groups
 *
 * @package Magmodules\AlternateHreflang\Model\System\Config\Source
 */
class Groups implements ArrayInterface
{

    /**
     * @var array
     */
    private $groups = [];

    /**
     * Returns array of groups
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->groups) {
            for ($x = 1; $x <= 20; $x++) {
                $this->groups[] = ['value' => $x, 'label' => 'Group ' . $x];
            }
        }

        return $this->groups;
    }
}
