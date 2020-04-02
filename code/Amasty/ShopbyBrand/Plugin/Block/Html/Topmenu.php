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
 * Class Topmenu
 *
 * @package Amasty\ShopbyBrand\Plugin\Block\Html
 */
class Topmenu
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\ShopbyBrand\Block\BrandsPopup
     */
    protected $brandsPopup;

    /**
     * @var string
     */
    private $label;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $url,
        \Amasty\ShopbyBrand\Helper\Data $helper,
        \Amasty\ShopbyBrand\Block\BrandsPopup $brandsPopup
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->url = $url;
        $this->brandsPopup = $brandsPopup;
    }

    /**
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param string $html
     * @return string
     */
    public function afterGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $html
    ) {
        if (!$this->isEnabled()) {
            return $html;
        }

        $brandPopup = $this->brandsPopup->toHtml();
        if ($brandPopup) {
            $html = $this->getPosition() == TopmenuSource::DISPLAY_FIRST ?
                $brandPopup . $html :
                $html . $brandPopup;
        }

        return $html;
    }

    /**
     * @return array
     */
    protected function _getNodeAsArray()
    {
        $url = $this->helper->getAllBrandsUrl();
        return [
            'name' => $this->getLabel(),
            'id' => 'amasty_shopby_brand_allbrands',
            'url' => $url,
            'has_active' => false,
            'is_active' => $url == $this->url->getCurrentUrl()
        ];
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

        return $this->getPosition() == $topMenuEnabled && $brandExist;
    }

    /**
     * @return int
     */
    protected function getPosition()
    {
        return TopmenuSource::DISPLAY_FIRST;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if ($this->label) {
            $label = $this->label;
        } else {
            $label = $this->scopeConfig->getValue(
                'amshopby_brand/general/menu_item_label',
                ScopeInterface::SCOPE_STORE
            );
        }

        return $label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }
}
