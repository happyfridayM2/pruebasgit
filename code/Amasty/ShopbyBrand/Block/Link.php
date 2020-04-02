<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Block;

use Magento\Store\Model\ScopeInterface;

/**
 * @api
 */
class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $helper;

    /**
     * Link constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Amasty\ShopbyBrand\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\ShopbyBrand\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    protected function _construct()
    {
        $this->setLabel((string)$this->_scopeConfig
            ->getValue('amshopby_brand/general/menu_item_label', ScopeInterface::SCOPE_STORE));
        parent::_construct();
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->helper->getAllBrandsUrl();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->isEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * @return bool
     */
    private function isEnabled()
    {
        return (bool) $this->_scopeConfig
            ->getValue('amshopby_brand/general/top_links', ScopeInterface::SCOPE_STORE);
    }
}
