<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Helper;

use Amasty\ShopbyBase\Helper\Data as BaseHelper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Url
 *
 * @package Amasty\ShopbySeo\Helper
 */
class Url extends AbstractHelper
{
    const CATALOG_MODULE_NAME = 'catalog';

    const CATEGORY_FILTER_PARAM = 'cat';

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var string
     */
    private $paramsDelimiter;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var bool|null
     */
    private $isAddSuffixToShopby = null;

    /**
     * @var int[]
     */
    private $filterPositions;

    /**
     * @var UrlParser
     */
    private $urlParser;

    /**
     * @var array
     */
    private $allowedModules = [
        'catalog',
        'ambrand',
        'amshopby'
    ];

    private $disallowedControllers = [
        'product_compare'
    ];

    /**
     * @var array
     */
    private $disallowedPathes = [
        'media/'
    ];

    /**
     * @var array
     */
    private $queryParams = [];

    /**
     * @var string
     */
    private $identifier = '';

    /**
     * @var bool
     */
    private $hasSeoAliases = false;

    /**
     * @var string
     */
    private $originalIdentifier;

    /**
     * @var bool
     */
    private $hasUrlSuffix = false;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(
        Context $context,
        Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\ShopbySeo\Helper\UrlParser $urlParser,
        \Amasty\ShopbySeo\Helper\Config $config,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->urlParser = $urlParser;
        $this->config = $config;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_getRequest();
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function seofyUrl($url)
    {
        if (!$this->initialize($url)) {
            return $url;
        }

        $identifier = $this->removeCategorySuffix($this->identifier);

        if ($this->isSeoUrlEnabled()) {
            $identifier = $this->injectAliases($identifier);
            $identifier = ltrim($identifier, DIRECTORY_SEPARATOR);
        }

        if ($this->hasUrlSuffix || $this->getRequest()->getMetaData(Data::SEO_REDIRECT_MISSED_SUFFIX_FLAG)) {
            $identifier = $this->addCategorySuffix($identifier);
        }

        if ($this->identifier !== $identifier) {
            if ($this->hasParams()) {
                $identifier .= '?' . $this->buildQuery();
            }
            $url = str_replace(trim($this->originalIdentifier, '/'), $identifier, $url);
        }

        return $url;
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    public function modifySeoIdentifier($identifier)
    {
        return $identifier;
    }

    /**
     * @param string $identifier
     * @param array $aliases
     *
     * @return string
     */
    public function modifySeoIdentifierByAlias($identifier, $aliases = [])
    {
        return $identifier;
    }

    /**
     * @return bool
     */
    private function isCatalog()
    {
        return !$this->dataPersistor->get(BaseHelper::SHOPBY_BRAND_POPUP)
            && (!$this->getRequest()->getModuleName() == self::CATALOG_MODULE_NAME
                || $this->hasCategoryFilterParam());
    }

    /**
     * @return bool
     */
    public function hasCategoryFilterParam()
    {
        return (bool)$this->getParam(self::CATEGORY_FILTER_PARAM);
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    private function initialize($url)
    {
        if (!in_array($this->getRequest()->getModuleName(), $this->allowedModules)) {
            return false;
        }

        // @codingStandardsIgnoreLine
        $parsedUrl = parse_url($url);

        $queryPosition = strpos($url, '?');
        if ($queryPosition) {
            $url = substr($url, 0, $queryPosition);
        }

        $switchedStore = $this->dataPersistor->get(BaseHelper::SHOPBY_SWITCHER_STORE_ID);
        $store = $switchedStore ? $this->storeManager->getStore($switchedStore) : $this->storeManager->getStore();
        $baseUrl = $store->getBaseUrl();

        $this->identifier = substr($url, strlen($baseUrl));
        $this->hasSeoAliases = false;
        $this->queryParams = [];

        foreach ($this->disallowedPathes as $path) {
            if (strpos($this->identifier, $path) !== false) {
                return false;
            }
        }

        if (isset($parsedUrl['query'])) {
            $this->paramsDelimiter = strpos($parsedUrl['query'], '&amp;') !== false ? '&amp;' : '&';
            $this->parseQuery($parsedUrl['query']);
        }

        $this->originalIdentifier = $this->identifier . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');

        return true;
    }

    /**
     * @param string $query
     *
     * @return $this
     */
    public function parseQuery($query)
    {
        $queryParams = str_replace($this->paramsDelimiter, '&', $query);
        // @codingStandardsIgnoreLine
        parse_str($queryParams, $this->queryParams);

        return $this;
    }

    /**
     * @return array
     */
    private function getAllAliases()
    {
        $aliases = [];
        $attributeOptionsData = $this->helper->getOptionsSeoData();
        foreach ($this->getParams() as $paramName => $rawValues) {
            if ($this->helper->isAttributeSeoSignificant($paramName)
                && isset($attributeOptionsData[$paramName])
            ) {
                $optionsData = $attributeOptionsData[$paramName];
                if (is_array($rawValues)) {
                    foreach ($rawValues as $value) {
                        if (!array_key_exists($value, $optionsData)) {
                            continue;
                        }
                        $aliases[$paramName][] = $optionsData[$value];
                    }
                } elseif (array_key_exists($rawValues, $optionsData)) {
                    $aliases[$paramName][] = $optionsData[$rawValues];
                }
                $this->setParam($paramName, null);
            }
        }

        $this->sortAliases($aliases);

        return $aliases;
    }

    /**
     * @param $parsedParams
     *
     * @return $this
     */
    private function prepareParams($parsedParams)
    {
        $this->queryParams = array_merge_recursive($this->getParams(), $parsedParams);
        foreach ($this->queryParams as $paramName => $rawValues) {
            if (is_array($rawValues)) {
                if (is_array(current($rawValues))) {
                    continue;
                }
                $rawValues = implode(',', $rawValues);
            }
            $rawValues = array_unique(explode(',', str_replace('%2C', ',', $rawValues)));
            $this->setParam($paramName, $rawValues);
        }

        return $this;
    }

    /**
     * @param array $seoAliases
     */
    private function sortAliases(&$seoAliases)
    {
        $filterPositions = $this->getFilterPositions();
        if ($filterPositions) {
            uksort(
                $seoAliases,
                function ($first, $second) use ($filterPositions) {
                    if ($first == $second) {
                        return 0;
                    }

                    if (!isset($filterPositions[$first])) {
                        return 1;
                    }

                    if (!isset($filterPositions[$second])) {
                        return -1;
                    }

                    return $filterPositions[$first] - $filterPositions[$second];
                }
            );
        }
    }

    /**
     * @return int[]|null
     */
    private function getFilterPositions()
    {
        if ($this->filterPositions === null) {
            $allFilters = $this->coreRegistry->registry(\Amasty\Shopby\Model\Layer\FilterList::ALL_FILTERS_KEY);

            if (!$allFilters) {
                return null;
            }

            $this->filterPositions = [];
            $position = 0;

            foreach ($allFilters as $filter) {
                $code = $filter->getRequestVar();
                $this->filterPositions[$code] = $position;
                $position++;
            }
        }

        return $this->filterPositions;
    }

    /**
     * @param $routeUrl
     *
     * @return string
     */
    private function injectAliases($routeUrl)
    {
        if ($this->helper->getFilterWord()) {
            if (strpos($routeUrl, '/' . $this->helper->getFilterWord() . '/') !== false) {
                $filterWordPosition = strpos($routeUrl, '/' . $this->helper->getFilterWord() . '/');
                $seoPart = substr(
                    $routeUrl,
                    $filterWordPosition + strlen('/' . $this->helper->getFilterWord() . '/')
                );
                $routeUrl = substr($routeUrl, 0, $filterWordPosition);
            } else {
                $seoPart = '';
            }
            $parsedParams = $this->urlParser->parseSeoPart($seoPart);
        } else {
            $trimmedRouteUrl = trim($routeUrl, '/');
            if ($lastSlashPosition = strrpos($trimmedRouteUrl, "/")) {
                $seoPart = substr($trimmedRouteUrl, $lastSlashPosition + 1);
                $parsedParams = $this->urlParser->parseSeoPart($seoPart);
                if ($parsedParams) {
                    $routeUrl = substr($trimmedRouteUrl, 0, $lastSlashPosition + 1);
                }
            } else {
                $parsedParams = $this->urlParser->parseSeoPart($trimmedRouteUrl);
                if ($parsedParams) {
                    $routeUrl = '';
                }
            }
        }

        $this->prepareParams($parsedParams);
        $routeUrl = $this->modifySeoIdentifier($routeUrl);

        $allAliases = $this->getAllAliases();

        if ($allAliases) {
            $this->hasSeoAliases = true;
            $routeUrl = rtrim($this->modifySeoIdentifierByAlias($routeUrl, $allAliases), '/') . DIRECTORY_SEPARATOR;
            $routeUrl .= $this->helper->getFilterWord() ? $this->helper->getFilterWord() . DIRECTORY_SEPARATOR : '';
            $optionSeparator = $this->config->getOptionSeparator();
            $aliasString = '';

            foreach ($allAliases as $code => $alias) {
                $aliasString .= ($aliasString ? $optionSeparator : '')
                    . ($this->helper->isIncludeAttributeName() ? $code . $optionSeparator : '')
                    . implode($optionSeparator, $alias);
            }
            $routeUrl .= $aliasString;
        }

        return $routeUrl;
    }

    /**
     * @return string
     */
    private function buildQuery()
    {
        $params = $this->getParams();
        foreach ($params as $name => $value) {
            if (is_array($value)) {
                $params[$name] = implode(',', $value);
            }
        }
        $query = http_build_query($params);
        $query = str_replace($this->paramsDelimiter, '&', $query);

        return str_replace('&', $this->paramsDelimiter, $query);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private function addCategorySuffix($url)
    {
        $suffix = $this->getSeoSuffix();
        if (strlen($suffix)) {
            $url .= $suffix;
        }

        return $url;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function removeCategorySuffix($url)
    {
        $this->hasUrlSuffix = false;
        $suffix = $this->getSeoSuffix();

        if (strlen($suffix)) {
            $suffixPosition = strrpos($url, $suffix);

            if ($suffixPosition !== false && $suffixPosition == strlen($url) - strlen($suffix)) {
                $this->hasUrlSuffix = true;
                $url = substr($url, 0, $suffixPosition);
            }
        }

        return $url;
    }

    /**
     * @return bool
     */
    public function isSeoUrlEnabled()
    {
        return $this->config->isSeoUrlEnabled();
    }

    /**
     * @return bool
     */
    public function getAddSuffixSettingValue()
    {
        return $this->scopeConfig->isSetFlag(
            'amasty_shopby_seo/url/add_suffix_shopby',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool|null
     */
    public function isAddSuffixToShopby()
    {
        if ($this->isAddSuffixToShopby === null) {
            $moduleName = $this->getRequest()->getModuleName();
            $controllerName = $this->getRequest()->getControllerName();
            $isModuleAllowed = in_array($moduleName, $this->allowedModules, true);
            $isControllerAllowed = !in_array($controllerName, $this->disallowedControllers, true);
            $isSuffixNotEmpty = (bool)strlen($this->getSeoSuffix());

            if ($isModuleAllowed && $isControllerAllowed && $isSuffixNotEmpty) {
                return $this->getAddSuffixSettingValue();
            }
        }

        return $this->isAddSuffixToShopby;
    }

    /**
     * @return string
     */
    public function getSeoSuffix()
    {
        return (string)$this->scopeConfig
            ->getValue('catalog/seo/category_url_suffix', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $paramName
     *
     * @return mixed
     */
    public function getParam($paramName)
    {
        return isset($this->queryParams[$paramName]) ? $this->queryParams[$paramName] : null;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->queryParams;
    }

    /**
     * @return bool
     */
    public function hasParams()
    {
        return !empty($this->queryParams);
    }

    /**
     * @param string $paramName
     * @param null $paramValue
     *
     * @return $this
     */
    public function setParam($paramName, $paramValue = null)
    {
        if ($paramValue === null) {
            unset($this->queryParams[$paramName]);
        } else {
            $this->queryParams[$paramName] = $paramValue;
        }

        return $this;
    }
}
