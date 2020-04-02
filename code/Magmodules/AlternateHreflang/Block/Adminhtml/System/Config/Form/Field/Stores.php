<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magmodules\AlternateHreflang\Model\System\Config\Source\Stores as StoresSource;

/**
 * Class Stores
 *
 * @package Magmodules\AlternateHreflang\Block\Adminhtml\System\Config\Form\Field
 */
class Stores extends Select
{

    /**
     * @var array
     */
    private $stores = [];
    /**
     * @var StoresSource
     */
    private $storesSource;

    /**
     * Stores constructor.
     *
     * @param Context $context
     * @param StoresSource $storesSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoresSource $storesSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storesSource = $storesSource;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->getStoresSource() as $store) {
                if (isset($store['value']) && $store['value'] && isset($store['label']) && $store['label']) {
                    $this->addOption($store['value'], $store['label']);
                }
            }
        }

        return parent::_toHtml();
    }

    /**
     * @return array of stores
     */
    public function getStoresSource()
    {
        if (!$this->stores) {
            $this->stores = $this->storesSource->toOptionArray();
        }

        return $this->stores;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
