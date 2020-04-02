<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Helper;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\Cache\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;
use Magento\Framework\App\Cache;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\CollectionFactory;
use Amasty\Shopby\Helper\Group;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory as OptionSettingCollectionFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class Data
 * @package Amasty\ShopbySeo\Helper
 */
class Data extends AbstractHelper
{
    const CANONICAL_ROOT = 'amasty_shopby_seo/canonical/root';
    const CANONICAL_CATEGORY = 'amasty_shopby_seo/canonical/category';
    const AMASTY_SHOPBY_SEO_URL_SPECIAL_CHAR = 'amasty_shopby_seo/url/special_char';
    const AMASTY_SHOPBY_SEO_URL_ATTRIBUTE_NAME = 'amasty_shopby_seo/url/attribute_name';
    const AMASTY_SHOPBY_SEO_URL_FILTER_WORD = 'amasty_shopby_seo/url/filter_word';
    const AMSHOPBY_ROOT_GENERAL_URL = 'amshopby_root/general/url';
    const AMSHOPBY_SEO_PAGE_META_TITLE = 'amasty_shopby_seo/other/page_meta_title';
    const AMSHOPBY_SEO_PAGE_META_DESCR = 'amasty_shopby_seo/other/page_meta_descriprion';
    const AMSHOPBY_SEO_REL_NOFOLLOW = 'amasty_shopby_seo/robots/rel_nofollow';
    const SKIP_REQUEST_FLAG = 'shopby_seo_skip_request_flag';
    const SEO_REDIRECT_FLAG = 'shopby_seo_redirect_flag';
    const SEO_REDIRECT_MISSED_SUFFIX_FLAG = 'shopby_seo_missed_suffix_redirect_flag';
    const HAS_PARSED_PARAMS = 'shopby_seo_has_parsed_params_flag';
    const HAS_ROUTE_PARAMS = 'shopby_seo_has_route_params_flag';
    const IS_MODULE_ENABLED = 'amasty_shopby_seo/url/mode';

    /**
     * @var CollectionFactory
     */
    private $settingCollectionFactory;

    /**
     * @var Option\CollectionFactory
     */
    private $optionCollectionFactory;

    /**
     * @var  OptionSettingCollectionFactory
     */
    private $optionSettingCollectionFactory;

    /**
     * @var  StoreManager
     */
    private $storeManager;

    /**
     * @var  \Magento\Catalog\Model\Product\Url
     */
    private $productUrl;

    /**
     * @var  Type
     */
    private $cache;

    /**
     * @var Cache\StateInterface
     */
    private $cacheState;

    /**
     * @var Group
     */
    private $groupHelper;

    /**
     * @var array|null
     */
    private $seoSignificantAttributeCodes;

    /**
     * @var array|null
     */
    private $optionsSeoData;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var array
     */
    private $skipRequestIdentifiers = [
        'catalog/category/',
        'catalog/product/',
        'cms/page/',
        'amasty_xsearch/',
        'customer/',
        'checkout/',
        'catalogsearch'
    ];

    public function __construct(
        Context $context,
        CollectionFactory $settingCollectionFactory,
        Option\CollectionFactory $optionCollectionFactory,
        \Magento\Catalog\Model\Product\Url $productUrl,
        OptionSettingCollectionFactory $optionSettingCollectionFactory,
        StoreManager $storeManager,
        Cache $cache,
        Group $groupHelper,
        \Amasty\ShopbySeo\Helper\Config $configHelper,
        Cache\StateInterface $cacheState,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
    ) {
        parent::__construct($context);
        $this->settingCollectionFactory = $settingCollectionFactory;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->optionSettingCollectionFactory = $optionSettingCollectionFactory;
        $this->storeManager = $storeManager;
        $this->productUrl = $productUrl;
        $this->cache = $cache;
        $this->cacheState = $cacheState;
        $this->groupHelper = $groupHelper;
        $this->configHelper = $configHelper;
        $this->urlFinder = $urlFinder;
    }

    /**
     * @return array
     */
    public function getOptionsSeoData()
    {
        $cache_id = 'amshopby_seo_options_data' . $this->storeManager->getStore()->getId();
        if ($this->optionsSeoData === null && $this->cacheState->isEnabled(Type::TYPE_IDENTIFIER)) {
            $cached = $this->cache->load($cache_id);
            if ($cached !== false) {
                $this->optionsSeoData = \Zend\Serializer\Serializer::unserialize($cached);
            }
        }
        if ($this->optionsSeoData === null) {
            $this->optionsSeoData = [];
            $aliasHash = [];

            $dynamicAliases = $this->loadDynamicAliasesExcluding(array_values($aliasHash));
            $ids = [];
            foreach ($dynamicAliases as $row) {
                $attributeCode = isset($row['attribute_code']) ? $row['attribute_code'] : '';
                if (!array_key_exists($row['attribute_id'], $ids)) {
                    $ids[$row['attribute_id']] = $attributeCode;
                }

                $alias = $this->buildUniqueAlias($row['value'], $aliasHash);
                $optionId = $row['option_id'];
                $this->optionsSeoData[$row['attribute_code']][$optionId] = $alias;
                $aliasHash[$alias] = $optionId;
            }
            $hardcodedAliases = $this->loadHardcodedAliases();
            foreach ($hardcodedAliases as $row) {
                if (strpos($row['filter_code'], 'attr_') === 0) {
                    $attributeCode = substr($row['filter_code'], strlen('attr_'));
                } else {
                    $attributeCode = '';
                }
                if (in_array($attributeCode, $ids)) {
                    $alias = $this->buildUniqueAlias($row['url_alias'], $aliasHash, $row['value']);
                    $this->optionsSeoData[$attributeCode][$row['value']] = $alias;
                    $aliasHash[$alias] = $row['value'];
                }
            }
            foreach ($ids as $id => $code) {
                $data = $this->groupHelper->getAliasGroup($id);
                if ($data) {
                    foreach ($data as $key => $record) {
                        $alias = $this->buildUniqueAlias($record, $aliasHash);
                        $this->optionsSeoData[$code][$key] = $alias;
                        $aliasHash[$record] = $key;
                    }
                }
            }
            if ($this->cacheState->isEnabled(Type::TYPE_IDENTIFIER)) {
                $this->cache->save(
                    \Zend\Serializer\Serializer::serialize($this->optionsSeoData),
                    $cache_id,
                    [Type::CACHE_TAG]
                );
            }
        }

        return $this->optionsSeoData;
    }

    /**
     * @return array
     */
    private function loadHardcodedAliases()
    {
        $aliases = [];
        if ($this->configHelper->isSeoUrlEnabled()) {
            $storeId = $this->storeManager->getStore()->getId();
            $aliases = $this->optionSettingCollectionFactory->create()->getHardcodedAliases($storeId);
        }

        return $aliases;
    }

    /**
     * @param array $excludeOptionIds
     * @return array
     */
    private function loadDynamicAliasesExcluding($excludeOptionIds = [])
    {
        $seoAttributeCodes = $this->getSeoSignificantAttributeCodes();

        $collection = $this->optionCollectionFactory->create();
        $collection->join(['a' => 'eav_attribute'], 'a.attribute_id = main_table.attribute_id', ['attribute_code']);
        $collection->addFieldToFilter('attribute_code', ['in' => $seoAttributeCodes]);
        $collection->setStoreFilter();
        $select = $collection->getSelect();
        if ($excludeOptionIds) {
            $select->where('`main_table`.`option_id` NOT IN (' . join(',', $excludeOptionIds) . ')');
        }
        $statement = $select->query();
        $rows = $statement->fetchAll();
        return $rows;
    }

    /**
     * @return array
     */
    public function getSeoSignificantAttributeCodes()
    {
        if ($this->seoSignificantAttributeCodes === null) {
            $filterCodes = [];

            if ($this->configHelper->isSeoUrlEnabled()) {
                $collection = $this->settingCollectionFactory->create();
                $collection->addFieldToFilter(FilterSettingInterface::IS_SEO_SIGNIFICANT, 1);
                $filterCodes = $collection->getColumnValues(FilterSettingInterface::FILTER_CODE);
                array_walk($filterCodes, function (&$code) {
                    if (substr($code, 0, 5) == \Amasty\ShopbyBase\Helper\FilterSetting::ATTR_PREFIX) {
                        $code = substr($code, 5);
                    }
                });
            }

            $this->seoSignificantAttributeCodes = $filterCodes;
        }

        return $this->seoSignificantAttributeCodes;
    }

    /**
     * @param $attribute
     * @return bool
     */
    public function isAttributeSeoSignificant($attribute)
    {
        if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute) {
            $attribute = $attribute->getAttributeCode();
        }
        $codes = $this->getSeoSignificantAttributeCodes();
        return in_array($attribute, $codes);
    }

    /**
     * @param $value
     * @param $hash
     * @param string $optionId
     * @return mixed|string
     */
    private function buildUniqueAlias($value, $hash, $optionId = '')
    {
        // @codingStandardsIgnoreLine
        $value = html_entity_decode($value, ENT_QUOTES);

        if (preg_match('@^[\d\.]+$@s', $value)) {
            $format = $value;
        } else {
            $format = $this->productUrl->formatUrlKey($value);
        }
        if ($format == '') {
            // Magento formats '-' as ''
            $format = '-';
        }

        $format = str_replace('-', $this->getSpecialChar(), $format);

        $unique = $format;
        $i = 1;
        while (array_key_exists($unique, $hash)) {
            if ($hash[$unique] !== $optionId) {
                $unique = $format . $this->getSpecialChar() . ($i++);
            } else {
                unset($hash[$unique]);
            }
        }
        
        return $unique;
    }

    /**
     * @return string
     */
    public function getSpecialChar()
    {
        return $this->scopeConfig->getValue(self::AMASTY_SHOPBY_SEO_URL_SPECIAL_CHAR, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getCanonicalRoot()
    {
        return $this->scopeConfig->getValue(self::CANONICAL_ROOT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getCanonicalCategory()
    {
        return $this->scopeConfig->getValue(self::CANONICAL_CATEGORY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getGeneralUrl()
    {
        return $this->scopeConfig->getValue(self::AMSHOPBY_ROOT_GENERAL_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isIncludeAttributeName()
    {
        return $this->scopeConfig->getValue(self::AMASTY_SHOPBY_SEO_URL_ATTRIBUTE_NAME, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getFilterWord()
    {
        return $this->scopeConfig->getValue(self::AMASTY_SHOPBY_SEO_URL_FILTER_WORD, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function isAddPageToMetaTitleEnabled()
    {
        return $this->scopeConfig->getValue(self::AMSHOPBY_SEO_PAGE_META_TITLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function isEnableRelNofollow()
    {
        return $this->scopeConfig->getValue(self::AMSHOPBY_SEO_REL_NOFOLLOW, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function isAddPageToMetaDescriprionEnabled()
    {
        return $this->scopeConfig->getValue(self::AMSHOPBY_SEO_PAGE_META_DESCR, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return \Magento\Framework\UrlInterface
     */
    public function getUrlBuilder()
    {
        return $this->_urlBuilder;
    }

    /**
     * @param RequestInterface $request
     * @param bool $allowEmptyModuleName = false
     * @return bool;
     */
    public function isAllowedRequest(RequestInterface $request, $allowEmptyModuleName = false)
    {
        if (!$allowEmptyModuleName && !$request->getModuleName()) {
            return false;
        }

        $identifier = ltrim($request->getOriginalPathInfo(), '/');
        if (!empty($identifier)) {
            foreach ($this->skipRequestIdentifiers as $skipRequestIdentifier) {
                if (strpos($identifier, $skipRequestIdentifier) === 0) {
                    return false;
                }
            }

            $rewrite = $this->urlFinder->findOneByData([
                UrlRewrite::REQUEST_PATH => $identifier,
                UrlRewrite::STORE_ID => $this->storeManager->getStore()->getId(),
            ]);
            if ($rewrite !== null) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isModuleEnabled()
    {
        return !!$this->scopeConfig->getValue(self::IS_MODULE_ENABLED, ScopeInterface::SCOPE_STORE);
    }
}
