<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Block\System\Config\Form\Field;

class Label extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->getModuleManager()->isEnabled('Amasty_SeoToolKit')) {
            $element->setValue('Installed');
            $element->setHtmlId('instaled_toolkit');
        } else {
            $element->setValue('Not installed');
            $element->setHtmlId('not_instaled_toolkit');
        }

        return parent::render($element);
    }
}
