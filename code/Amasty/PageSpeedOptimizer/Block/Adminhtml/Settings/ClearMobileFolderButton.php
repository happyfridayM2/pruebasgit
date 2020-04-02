<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */

namespace Amasty\PageSpeedOptimizer\Block\Adminhtml\Settings;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class ClearMobileFolderButton creates button for
 * \Amasty\PageSpeedOptimizer\Controller\Adminhtml\Image\ClearMobileFolder action
 */
class ClearMobileFolderButton extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setData('value', __("Clear Mobile Images Folder"));
        $element->setData('class', "action-default amoptimizer-btn");
        $element->setData('onclick', "location.href = '" . $this->getActionUrl() . "'");

        return parent::_getElementHtml($element);
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->_urlBuilder->getUrl('amoptimizer/image/clearMobileFolder');
    }
}
