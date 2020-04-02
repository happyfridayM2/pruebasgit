<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Navigation\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\Store;

/**
 * Class Tooltip
 * @package Amasty\Shopby\Block\Navigation\Widget
 */
class Tooltip extends \Magento\Framework\View\Element\Template implements WidgetInterface
{
    /**
     * @var \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    protected $filterSetting;

    /**
     * @var string
     */
    protected $_template = 'layer/widget/tooltip.phtml';

    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    protected $helper;

    /**
     * @var \Amasty\Shopby\Helper\Group
     */
    private $groupHelper;

    public function __construct(
        Template\Context $context,
        \Amasty\Shopby\Helper\Data $helper,
        \Amasty\Shopby\Helper\Group $groupHelper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->groupHelper = $groupHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Amasty\ShopbyBase\Api\Data\FilterSettingInterface $filterSetting
     * @return $this
     */
    public function setFilterSetting(\Amasty\ShopbyBase\Api\Data\FilterSettingInterface $filterSetting)
    {
        $this->filterSetting = $filterSetting;
        return $this;
    }

    /**
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getFilterSetting()
    {
        return $this->filterSetting;
    }

    /**
     * @return string
     */
    public function getTooltipUrl()
    {
        return $this->helper->getTooltipUrl();
    }

    /**
     * @return string
     */
    public function getTooltipTemplate()
    {
        return sprintf(
            '<span class="tooltip amshopby-filter-tooltip" title="{content}"><img src="%s"></span>',
            $this->escapeUrl($this->getTooltipUrl())
        );
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return null|string
     */
    public function getContent()
    {
        if ($tooltip = $this->getFilterSetting()->getTooltip()) {
            $tooltip = strip_tags($this->groupHelper->chooseGroupLabel($tooltip));
        }

        return $tooltip;
    }

    /*
     * @param  mixed $valueToEncode
     * @param  boolean $cycleCheck
     * @param  array $options
     * @return string
     */
    /**
     * @param $valueToEncode
     * @param bool $cycleCheck
     * @param array $options
     * @return string
     */
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = [])
    {
        return \Zend_Json::encode($valueToEncode, $cycleCheck, $options);
    }
}
