<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Framework\Url;

use Amasty\ShopbyBrand\Helper\Content;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class RouteParamsResolver
 * @package Amasty\Shopby\Plugin\Framework\Url
 */
class RouteParamsResolver
{
    /**
     * @var \Amasty\Shopby\Model\Resolver
     */
    protected $amResolver;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $layer;

    /**
     * @var \Magento\Framework\Url\QueryParamsResolverInterface
     */
    protected $queryParamsResolver;

    /**
     * @var \Amasty\Shopby\Model\Request
     */
    protected $shopbyRequest;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Content
     */
    private $contentHelper;

    /**
     * RouteParamsResolver constructor.
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Amasty\Shopby\Model\Resolver $amResolver
     * @param \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver
     * @param \Amasty\Shopby\Model\Request $shopbyRequest
     * @param ScopeConfigInterface $scopeConfig
     * @param Content $contentHelper
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Amasty\Shopby\Model\Resolver $amResolver,
        \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver,
        \Amasty\Shopby\Model\Request $shopbyRequest,
        ScopeConfigInterface $scopeConfig,
        Content $contentHelper
    ) {
        $this->amResolver = $amResolver;
        $this->layerResolver = $layerResolver;
        $this->queryParamsResolver = $queryParamsResolver;
        $this->shopbyRequest = $shopbyRequest;
        $this->scopeConfig = $scopeConfig;
        $this->contentHelper = $contentHelper;
    }

    /**
     * @param \Magento\Framework\Url\RouteParamsResolver $subject
     * @param \Closure $proceed
     * @param array $data
     * @param bool|true $unsetOldParams
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundSetRouteParams(
        \Magento\Framework\Url\RouteParamsResolver $subject,
        \Closure $proceed,
        array $data,
        $unsetOldParams = true
    ) {
        if (!array_key_exists('_current', $data)) {
            return $proceed($data, $unsetOldParams);
        }

        $queryParams = $this->queryParamsResolver->getQueryParams();

        $filters = $this->getLayer()->getState()->getFilters();
        foreach ($filters as $filter) {
            $filterParam = $this->shopbyRequest->getFilterParam($filter->getFilter());
            if (!empty($filterParam)) {
                $queryParams[$filter->getFilter()->getRequestVar()] = $filterParam;
            }
        }

        $queryParams[\Amasty\Shopby\Block\Navigation\UrlModifier::VAR_REPLACE_URL] = null;
        $queryParams['amshopby'] = null;

        if (array_key_exists('price', $queryParams)) {
            $data['price'] = null; //fix for catalogsearxch pages
        }

        $result = $proceed($data, $unsetOldParams);
        $this->queryParamsResolver->addQueryParams($queryParams);

        return $result;
    }

    /**
     * @return \Magento\Catalog\Model\Layer
     */
    protected function getLayer()
    {
        if (!$this->layer) {
            $this->layer = $this->amResolver->loadFromParent($this->layerResolver)->get();
        }

        return $this->layer;
    }
}
