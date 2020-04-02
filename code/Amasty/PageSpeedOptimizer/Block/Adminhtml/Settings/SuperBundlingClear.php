<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */

namespace Amasty\PageSpeedOptimizer\Block\Adminhtml\Settings;

use Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\CollectionFactory;
use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SuperBundlingClear extends Field
{
    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\Collection
     */
    private $collection;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        CollectionFactory $collectionFactory,
        ConfigProvider $configProvider,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collection = $collectionFactory->create();
        $this->configProvider = $configProvider;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setData('value', __("Clear Bundle"));
        $element->setData('class', "action-default");
        $element->setData('onclick', "location.href = '" . $this->getActionUrl() . "'");

        if ($this->configProvider->getBundleStep() || !$this->collection->getSize()) {
            $element->setData('readonly', true);

            return parent::_getElementHtml($element);
        }

        return '<div style="margin-bottom:10px">'
            . __('The JS optimization is finished. Please check your website.')
            . '<br>' . __('Use Clear Bundle button to roll back the JavaScript optimization.')
            . '</div>'
            . parent::_getElementHtml($element);
    }

    public function getActionUrl()
    {
        return $this->_urlBuilder->getUrl('amoptimizer/bundle/clear');
    }
}
