<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magmodules\AlternateHreflang\Helper\General;

/**
 * Class Stores
 *
 * @package Magmodules\AlternateHreflang\Model\System\Config\Source
 */
class Stores implements ArrayInterface
{

    /**
     * @var General
     */
    private $generalHelper;
    /**
     * @var array
     */
    private $stores = [];

    /**
     * Stores constructor.
     *
     * @param General $generalHelper
     */
    public function __construct(
        General $generalHelper
    ) {
        $this->generalHelper = $generalHelper;
    }

    /**
     * Returns array of availabe stores
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->stores) {
            foreach ($this->generalHelper->getStores() as $store) {
                $this->stores[] = [
                    'value' => $store->getId(),
                    'label' => $store->getName() . ' (' . $store->getCode() . ')'
                ];
            }
        }
        return $this->stores;
    }
}
