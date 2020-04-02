<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Block\Adminhtml\System\Config\Form\Field;

class Label extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->getModuleManager()->isEnabled('Amasty_XmlSitemap')) {
            $element->setValue(__("Installed"));
            $element->setHtmlId('instaled_sitemap');
            $element->setComment(__(
                "To manage sitemaps please click <a href='%1'>here</a>",
                $this->getUrl('amxmlsitemap/sitemap')
            ));
        } else {
            $element->setValue(__('Not installed'));
            $element->setHtmlId('not_instaled_sitemap');
        }

        return parent::render($element);
    }
}
