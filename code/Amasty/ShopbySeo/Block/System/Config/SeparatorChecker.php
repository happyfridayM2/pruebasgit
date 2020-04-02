<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Block\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class SeparatorChecker
 * @package Amasty\ShopbySeo\Block\System\Config
 */
class SeparatorChecker extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var string
     */
    private $userGuide = 'https://amasty.com/docs/doku.php?id=magento_2:improved_layered_navigation#seo_settings';

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Amasty_ShopbySeo::system/config/checker.phtml');

        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getUserGuideUrl()
    {
        return $this->userGuide;
    }
}
