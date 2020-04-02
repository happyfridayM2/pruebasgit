<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\RequestInterface;
use Amasty\ShopbyBase\Helper\Data;

/**
 * Class AllowedRoute
 */
class AllowedRoute
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var  \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function isRouteAllowed(RequestInterface $request)
    {
        if ($this->isEnabled()) {
            return true;
        }

        $brandCode = $this->getBrandCode();
        if ($brandCode) {
            $seoParams = $this->registry->registry(Data::SHOPBY_SEO_PARSED_PARAMS);
            $seoBrandPresent = isset($seoParams) && array_key_exists($brandCode, $seoParams);
            if ($request->getParam($brandCode) || $seoBrandPresent) {
                return true;
            }
        }

        $this->registry->unregister(Data::SHOPBY_SEO_PARSED_PARAMS);

        return false;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag('amshopby_root/general/enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getBrandCode()
    {
        return $this->scopeConfig ->getValue('amshopby_brand/general/attribute_code', ScopeInterface::SCOPE_STORE);
    }
}
