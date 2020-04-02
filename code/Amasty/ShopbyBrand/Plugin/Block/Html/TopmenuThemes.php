<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Plugin\Block\Html;

use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\ScopeInterface;
use Amasty\ShopbyBrand\Model\Source\TopmenuLink as TopmenuSource;

/**
 * Class TopmenuThemes
 *
 * @package Amasty\ShopbyBrand\Plugin\Block\Html
 */
class TopmenuThemes extends Topmenu
{
    /**
     * @param $subject
     * @param $html
     * @return string
     */
    public function afterRenderCategoriesMenuHtml(
        $subject,
        $html
    ) {
        $position = $topMenuEnabled = $this->scopeConfig->getValue(
            'amshopby_brand/general/topmenu_enabled',
            ScopeInterface::SCOPE_STORE
        );

        if ($position) {
            if ($subject instanceof \Smartwave\Megamenu\Block\Topmenu) {
                $this->brandsPopup->setPortoTheme();
            } elseif ($subject instanceof \Infortis\UltraMegamenu\Block\Navigation) {
                $this->brandsPopup->setUltimoTheme();
            }
            $htmlBrand = $this->generateHtml($this->_getNodeAsArray());
            if ($position == TopmenuSource::DISPLAY_FIRST) {
                $html = $htmlBrand . $html;
            } else {
                $html .= $htmlBrand;
            }
        }

        return $html;
    }

    /**
     * @param $subject
     * @param $html
     * @return string
     */
    public function afterGetMegamenuHtml(
        $subject,
        $html
    ) {
        return $this->afterRenderCategoriesMenuHtml($subject, $html);
    }

    /**
     * @param $data
     * @return string
     */
    private function generateHtml($data)
    {
        return $this->brandsPopup->toHtml();
    }
}
