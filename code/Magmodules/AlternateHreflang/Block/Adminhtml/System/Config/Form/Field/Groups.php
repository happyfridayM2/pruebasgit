<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magmodules\AlternateHreflang\Model\System\Config\Source\Groups as GroupSource;

class Groups extends Select
{

    /**
     * @var array
     */
    private $groups = [];
    /**
     * @var GroupSource
     */
    private $groupSource;

    /**
     * Groups constructor.
     *
     * @param Context $context
     * @param GroupSource $groupSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        GroupSource $groupSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->groupSource = $groupSource;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getGroupSource() as $group) {
                $this->addOption($group['value'], $group['label']);
            }
        }

        return parent::_toHtml();
    }

    /**
     * Get all groups
     *
     * @return array
     */
    public function _getGroupSource()
    {
        if (!$this->groups) {
            $this->groups = $this->groupSource->toOptionArray();
        }

        return $this->groups;
    }

    /**
     * Sets name for input element
     *
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
