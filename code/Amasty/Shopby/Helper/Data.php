<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Helper;

use Magento\Catalog\Model\Layer;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Amasty\Shopby;
use Magento\Store\Model\ScopeInterface;
use Amasty\ShopbyBase\Helper\OptionSetting as OptionSettingHelper;

class Data extends AbstractHelper
{
    const UNFOLDED_OPTIONS_STATE = 'amshopby/general/unfolded_options_state';
    const AMSHOPBY_ROOT_GENERAL_URL_PATH = 'amshopby_root/general/url';
    const AMSHOPBY_ROOT_ENABLED_PATH = 'amshopby_root/general/enabled';
    const CATEGORY_FILTER_POSITION = 'amshopby/category_filter/position';
    const CATALOG_SEO_SUFFIX_PATH = 'catalog/seo/category_url_suffix';

    /**
     * @var FilterSetting
     */
    protected $settingHelper;

    /**
     * @var  Layer
     */
    protected $layer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Shopby\Model\Request
     */
    protected $shopbyRequest;

    /**
     * @var  Shopby\Model\Layer\FilterList
     */
    protected $filterList;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    private $swatchHelper;
    /**
     * @var OptionSettingHelper
     */
    private $optionSettingHelper;

    /**
     * @var \Amasty\ShopbyBase\Helper\Data
     */
    private $baseHelper;

    /**
     * @var \Amasty\ShopbyBase\Api\UrlBuilderInterface
     */
    private $amUrlBuilder;

    /**
     * @var Layer\Resolver
     */
    private $layerResolver;

    /**
     * @var \Amasty\ShopbyBase\Helper\PermissionHelper
     */
    private $permissionHelper;

    public function __construct(
        Context $context,
        FilterSetting $settingHelper,
        Layer\Resolver $layerResolver,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Shopby\Model\Request $shopbyRequest,
        Shopby\Model\Layer\FilterList $filterList,
        \Magento\Swatches\Helper\Data $swatchHelper,
        OptionSettingHelper $optionSettingHelper,
        \Magento\Framework\Registry $registry,
        \Amasty\ShopbyBase\Helper\Data $baseHelper,
        \Amasty\ShopbyBase\Helper\PermissionHelper $permissionHelper,
        \Amasty\ShopbyBase\Api\UrlBuilderInterface $amUrlBuilder
    ) {
        parent::__construct($context);
        $this->settingHelper = $settingHelper;
        $this->layerResolver = $layerResolver;
        $this->storeManager = $storeManager;
        $this->shopbyRequest = $shopbyRequest;
        $this->filterList = $filterList;
        $this->registry = $registry;
        $this->swatchHelper = $swatchHelper;
        $this->optionSettingHelper = $optionSettingHelper;
        $this->baseHelper = $baseHelper;
        $this->amUrlBuilder = $amUrlBuilder;
        $this->permissionHelper = $permissionHelper;
    }

    /**
     * @return array
     */
    public function getSelectedFiltersSettings()
    {
        $filters = $this->filterList->getAllFilters($this->getLayer());
        $result = [];
        foreach ($filters as $filter) {
            /** @var Layer\Filter\AbstractFilter $filter */
            $var = $filter->getRequestVar();
            if ($this->shopbyRequest->getParam($var) !== null) {
                $setting = $this->settingHelper->getSettingByLayerFilter($filter);
                $result[] = [
                    'filter' => $filter,
                    'setting' => $setting,
                ];
            }
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isAjaxEnabled()
    {
        return $this->scopeConfig->isSetFlag('amshopby/general/ajax_enabled', ScopeInterface::SCOPE_STORE)
            || $this->collectFilters();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTooltipUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $tooltipImage = $this->scopeConfig->getValue('amshopby/tooltips/image', ScopeInterface::SCOPE_STORE);
        if (empty($tooltipImage)) {
            return '';
        }
        return $baseUrl . $tooltipImage;
    }

    /**
     * @param Shopby\Model\Layer\Filter\Item $filterItem
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isFilterItemSelected(\Amasty\Shopby\Model\Layer\Filter\Item $filterItem)
    {
        $filter = $filterItem->getFilter();
        $data = $this->shopbyRequest->getFilterParam($filter);

        if (!empty($data)) {
            $ids = explode(',', $data);
            if (in_array($filterItem->getValue(), $ids)) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\Item[] $activeFilters
     * @return string
     */
    public function getAjaxCleanUrl($activeFilters)
    {
        $filterState = [];

        foreach ($activeFilters as $item) {
            $filterState[$item->getFilter()->getRequestVar()] = $item->getFilter()->getCleanValue();
        }

        $filterState['p'] = null;

        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $filterState;
        $params['_escape'] = true;

        return str_replace('&amp;', '&', $this->amUrlBuilder->getUrl('*/*/*', $params));
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        return $this->getLayer()->getCurrentCategory();
    }

    /**
     * @return string|null
     */
    public function getThumbnailPlaceholder()
    {
        return $this->scopeConfig->getValue('catalog/category_placeholder/thumbnail');
    }

    /**
     * @return int
     */
    public function getSubmitFiltersDesktop()
    {
        return $this->scopeConfig->getValue('amshopby/general/submit_filters_on_desktop', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function getSubmitFiltersMobile()
    {
        return $this->scopeConfig->getValue('amshopby/general/submit_filters_on_mobile', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function collectFilters()
    {
        if ($this->baseHelper->isMobile()) {
            $result = $this->getSubmitFiltersMobile();
        } else {
            $result = $this->getSubmitFiltersDesktop();
        }

        return (int)$result;
    }

    /**
     * @return int
     */
    public function getUnfoldedCount()
    {
        return (int)$this->scopeConfig->getValue(self::UNFOLDED_OPTIONS_STATE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param array $optionIds
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return array
     */
    public function getSwatchesFromImages($optionIds, \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute)
    {
        $swatches = [];
        if (!$this->swatchHelper->isVisualSwatch($attribute) && !$this->swatchHelper->isTextSwatch($attribute)) {
            /**
             * @TODO use collection method
             */
            foreach ($optionIds as $optionId) {
                $setting = $this->optionSettingHelper->getSettingByValue(
                    $optionId,
                    FilterSetting::ATTR_PREFIX . $attribute->getAttributeCode(),
                    $this->storeManager->getStore()->getId()
                );

                $swatches[$optionId] = [
                    'type' => 'option_image',
                    'value' => $setting->getSliderImageUrl()
                ];
            }
        }

        return $swatches;
    }

    /**
     * @return string
     */
    public function getAllProductsUrlKey()
    {
        return $this->scopeConfig->getValue(
            self::AMSHOPBY_ROOT_GENERAL_URL_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isAllProductsEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::AMSHOPBY_ROOT_ENABLED_PATH,
            ScopeInterface::SCOPE_STORE
        ) && $this->permissionHelper->checkPermissions();
    }

    /**
     * @return mixed
     */
    public function getCatalogSeoSuffix()
    {
        return (string)$this->scopeConfig->getValue(
            self::CATALOG_SEO_SUFFIX_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getCategoryPosition()
    {
        return $this->scopeConfig->getValue(self::CATEGORY_FILTER_POSITION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return Layer
     */
    public function getLayer()
    {
        if (!$this->layer) {
            $this->layer = $this->layerResolver->get();
        }
        return $this->layer;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return parent::_getRequest();
    }

    /**
     * @return bool
     */
    public function isBrandPage()
    {
        return $this->getRequest()->getFullActionName() == 'ambrand_index_index';
    }
}
