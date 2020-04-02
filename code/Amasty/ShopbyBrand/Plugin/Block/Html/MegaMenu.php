<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Plugin\Block\Html;

use Magento\Store\Model\ScopeInterface;
use Amasty\ShopbyBrand\Model\Source\TopmenuLink as TopmenuSource;

/**
 * Class MegaMenu
 *
 * @package Amasty\ShopbyBrand\Plugin\Block\Html
 */
class MegaMenu extends Topmenu
{
    /**
     * @param \Amasty\MegaMenu\Model\Menu\TreeResolver $subject
     * @param string $html
     * @return string
     */
    public function afterGetAdditionalLinks(
        \Amasty\MegaMenu\Model\Menu\TreeResolver $subject,
        $items
    ) {
        if (!$this->isEnabled()) {
            return $items;
        }

        $data = $this->_getNodeAsArray();
        $data['content'] = $this->brandsPopup->getOnlyContent();
        $data['width'] = 1;
        $items[] = $data;

        return $items;
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        $topMenuEnabled = $this->scopeConfig->getValue(
            'amshopby_brand/general/topmenu_enabled',
            ScopeInterface::SCOPE_STORE
        );

        $brandExist = $this->scopeConfig->getValue(
            'amshopby_brand/general/attribute_code',
            ScopeInterface::SCOPE_STORE
        );

        return $topMenuEnabled && $brandExist;
    }
}
