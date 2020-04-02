<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Amasty\ShopbyBase\Model\Integration\IntegrationFactory;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    const SHOPBY_MODULE_NAME = 'Amasty_Shopby';

    const SHOPBY_CATEGORY_INDEX = 'amasty_shopby_category_index';

    const SHOPBY_SEO_PARSED_PARAMS = 'amasty_shopby_seo_parsed_params';

    const SHOPBY_BRAND_POPUP = 'shopby_brand_popup';

    const SHOPBY_SWITCHER_STORE_ID = 'shopby_switcher_store_id';

    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    private $moduleList;

    /**
     * @var \Magento\Framework\Module\ModuleResource
     */
    private $moduleResource;

    /**
     * @var IntegrationFactory
     */
    private $integrationFactory;

    /**
     * @var \Zend_Http_UserAgent
     */
    private $userAgent;

    public function __construct(
        Context $context,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Framework\Module\ModuleResource $moduleResource,
        IntegrationFactory $integrationFactory,
        \Zend_Http_UserAgent $userAgent
    ) {
        parent::__construct($context);
        $this->moduleList = $moduleList;
        $this->moduleResource = $moduleResource;
        $this->integrationFactory = $integrationFactory;
        $this->userAgent = $userAgent;
    }

    /**
     * @return null
     */
    public function getShopbyVersion()
    {
        return $this->moduleResource->getDbVersion(self::SHOPBY_MODULE_NAME);
    }

    /**
     * @return bool
     */
    public function isShopbyInstalled()
    {
        return ($this->moduleList->getOne(self::SHOPBY_MODULE_NAME) !== null)
            && $this->getShopbyVersion();
    }

    /**
     * @return string
     */
    public function getBrandAttributeCode()
    {
        /** @var \Amasty\ShopbyBrand\Helper\Data|\Amasty\ShopbyBase\Model\Integration\DummyObject $brandHelper */
        $brandHelper = $this->integrationFactory->get(\Amasty\ShopbyBrand\Helper\Data::class, true);

        return (string)$brandHelper->getBrandAttributeCode();
    }

    /**
     * @return string
     */
    public function getBrandUrlKey()
    {
        /** @var \Amasty\ShopbyBrand\Helper\Data|\Amasty\ShopbyBase\Model\Integration\DummyObject $brandHelper */
        $brandHelper = $this->integrationFactory->get(\Amasty\ShopbyBrand\Helper\Data::class, true);

        return (string)$brandHelper->getBrandUrlKey();
    }

    /**
     * @return bool
     */
    public function isAddSuffixToShopby()
    {
        /** @var \Amasty\ShopbySeo\Helper\Data|\Amasty\ShopbyBase\Model\Integration\DummyObject $urlHelper */
        $urlHelper = $this->integrationFactory->get(\Amasty\ShopbySeo\Helper\Url::class, true);

        return $urlHelper->isAddSuffixToShopby();
    }

    /**
     * @return bool
     */
    public function isMobile()
    {
        return stristr($this->userAgent->getUserAgent(), 'mobi') !== false;
    }

    /**
     * @return bool
     */
    public function isEnableRelNofollow()
    {
        /** @var \Amasty\ShopbySeo\Helper\Data|\Amasty\ShopbyBase\Model\Integration\DummyObject $seoHelper */
        $seoHelper = $this->integrationFactory->get(\Amasty\ShopbySeo\Helper\Data::class, true);

        return $seoHelper->isEnableRelNofollow();
    }
}
