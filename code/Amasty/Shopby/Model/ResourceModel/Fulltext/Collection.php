<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\ResourceModel\Fulltext;

use Amasty\Shopby\Helper\Category;
use Amasty\Shopby\Helper\Group;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Phrase;

/**
 * Fulltext Collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * @var  QueryResponse
     */
    private $queryResponse;

    /**
     * Catalog search data
     *
     * @var \Magento\Search\Model\QueryFactory
     */
    private $queryFactory = null;

    /**
     * @var \Amasty\Shopby\Model\Request\Builder
     */
    private $requestBuilder;

    /**
     * @var \Magento\Search\Model\SearchEngine
     */
    private $searchEngine;

    /**
     * @var string
     */
    private $queryText;

    /**
     * @var string|null
     */
    private $relevanceOrderDirection;

    /**
     * @var string
     */
    private $searchRequestName;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory
     */
    private $temporaryStorageFactory;

    /**
     * @var \Amasty\Shopby\Model\Layer\Cms\Manager
     */
    private $cmsManager;

    /**
     * @var Stock
     */
    private $stockHelper;

    /**
     * @var Group
     */
    private $groupHelper;

    /**
     * @var \Amasty\Shopby\Model\Request\Builder
     */
    private $memRequestBuilder;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     * @param \Amasty\Shopby\Model\Request\BuilderFactory $requestBuilderFactory
     * @param \Magento\Search\Model\SearchEngine $searchEngine
     * @param \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory
     * @param \Amasty\Shopby\Model\Layer\Cms\Manager $cmsManager
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param Stock $stockHelper
     * @param Group $groupHelper
     * @param null $connection
     * @param string $searchRequestName
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Amasty\Shopby\Model\Request\BuilderFactory $requestBuilderFactory,
        \Magento\Search\Model\SearchEngine $searchEngine,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory,
        \Amasty\Shopby\Model\Layer\Cms\Manager $cmsManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        Stock $stockHelper,
        Group $groupHelper,
        $connection = null,
        $searchRequestName = 'catalog_view_container'
    ) {
        $this->queryFactory = $queryFactory;
        $this->searchRequestName = $searchRequestName;
        $this->groupHelper = $groupHelper;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection
        );

        $this->requestBuilder = $requestBuilderFactory->create();
        $this->searchEngine = $searchEngine;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->cmsManager = $cmsManager;
        $this->stockHelper = $stockHelper;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Add search query filter
     *
     * @param string $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $this->queryText = trim($this->queryText . ' ' . $query);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setRequestData($builder)
    {
        $this->_select->reset();
        $this->requestBuilder = $builder;
        $this->queryResponse = null;
        $this->_isFiltersRendered = false;
    }

    /**
     * @return \Amasty\Shopby\Model\Request\Builder
     */
    public function getMemRequestBuilder()
    {
        if ($this->memRequestBuilder === null) {
            $this->memRequestBuilder = clone $this->requestBuilder;
            $this->memRequestBuilder->bindDimension('scope', $this->getStoreId());
            if ($this->queryText) {
                $this->memRequestBuilder->bind('search_term', $this->queryText);
            }

            $priceRangeCalculation = $this->getPriceRangeCalculation();
            if ($priceRangeCalculation) {
                $this->memRequestBuilder->bind('price_dynamic_algorithm', $priceRangeCalculation);
            }

            $this->memRequestBuilder->setRequestName($this->searchRequestName);
        }
        return $this->memRequestBuilder;
    }

    /**
     * @return string
     */
    protected function getPriceRangeCalculation()
    {
        return $this->_scopeConfig->getValue(
            \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory::XML_PATH_RANGE_CALCULATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = Select::SQL_DESC)
    {
        if ($attribute === 'relevance') {
            $this->relevanceOrderDirection = $dir;
        } else {
            parent::setOrder($attribute, $dir);
        }

        return $this;
    }

    /**
     * Stub method for compatibility with other search engines
     *
     * @return $this
     */
    public function setGeneralDefaultQuery()
    {
        return $this;
    }

    /**
     * @param $field
     * @param QueryResponse|null $outerResponse
     * @return array
     */
    public function getFacetedData($field, QueryResponse $outerResponse = null)
    {
        if (!$outerResponse) {
            $this->_renderFilters();
        }

        $response = $outerResponse ?: $this->queryResponse;

        $aggregations = $response->getAggregations();
        $bucket = $aggregations->getBucket($field . '_bucket');
        $result = [];
        if (!$bucket) {
            throw new StateException(new Phrase('Bucket does not exist'));
        }

        foreach ($bucket->getValues() as $value) {
            $metrics = $value->getMetrics();
            $result[$metrics['value']] = $metrics;
        }

        return $result;
    }

    /**
     * @return $this
     */
    protected function _renderFilters()
    {
        $this->_filters = [];
        return parent::_renderFilters();
    }

    /**
     * Specify category filter for product collection
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return $this
     */
    public function addCategoryFilter(\Magento\Catalog\Model\Category $category)
    {
        $this->addFieldToFilter(Category::ATTRIBUTE_CODE, $category->getId());
        return parent::addCategoryFilter($category);
    }

    /**
     * Apply attribute filter to facet collection
     *
     * @param string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($this->queryResponse !== null) {
            throw new \RuntimeException('Illegal state');
        }

        if (!is_array($condition) || !in_array(key($condition), ['from', 'to'], true)) {
            // See app/code/Magento/Catalog/Model/ResourceModel/Product/Indexer/Eav/AbstractEav::_getIndexableAttributesCondition()
            // Visibility filter wasn't in index before 2.2
            if ($field != 'visibility'
                || version_compare($this->productMetadata->getVersion(), '2.2.0', '>=')) {
                $this->requestBuilder->bind($field, $condition);
            }
        } else {
            if (!empty($condition['from'])) {
                $this->requestBuilder->bind("{$field}.from", $condition['from']);
            }
            if (!empty($condition['to'])) {
                $this->requestBuilder->bind("{$field}.to", $condition['to']);
            }
        }

        return $this;
    }

    /**
     * Set product visibility filter for enabled products
     *
     * @param array $visibility
     * @return $this
     */
    public function setVisibility($visibility)
    {
        $this->addFieldToFilter('visibility', $visibility);
        return parent::setVisibility($visibility);
    }

    /**
     * Load product collection before filters rendering
     */
    protected function _renderFiltersBefore()
    {
        $this->requestBuilder->bindDimension('scope', $this->getStoreId());
        if ($this->queryText) {
            $this->requestBuilder->bind('search_term', $this->queryText);
        }

        $priceRangeCalculation = $this->getPriceRangeCalculation();
        if ($priceRangeCalculation) {
            $this->requestBuilder->bind('price_dynamic_algorithm', $priceRangeCalculation);
        }

        $this->requestBuilder->setRequestName($this->searchRequestName);
        $this->memRequestBuilder = clone $this->requestBuilder;
        $queryRequest = $this->requestBuilder->create();
        $this->queryResponse = $this->searchEngine->search($queryRequest);

        $temporaryStorage = $this->temporaryStorageFactory->create();
        $table = $temporaryStorage->storeApiDocuments($this->queryResponse->getIterator());

        $this->getSelect()->joinInner(
            [
                'search_result' => $table->getName(),
            ],
            'e.entity_id = search_result.' . TemporaryStorage::FIELD_ENTITY_ID,
            []
        );

        $this->cmsManager->setIndexStorageTable($table);

        parent::_renderFiltersBefore();
    }

    /**
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->setOrder('entity_id');
        $this->stockHelper->addIsInStockFilterToCollection($this);

        return parent::_beforeLoad();
    }

    /**
     * Add order by entity_id
     *
     * @return $this
     */
    protected function _renderOrders()
    {
        if (!$this->_isOrdersRendered) {
            if ($this->relevanceOrderDirection) {
                $this->getSelect()->order(
                    'search_result.'. TemporaryStorage::FIELD_SCORE . ' ' . $this->relevanceOrderDirection
                );
            }

            parent::_renderOrders();
        }

        return $this;
    }

    /**
     * Filter Product by Categories using index table
     *
     * @param array $categoriesFilter
     * @return $this
     */
    public function addIndexCategoriesFilter(array $categoriesFilter)
    {
        foreach ($categoriesFilter as $conditionType => $values) {
            $categorySelect = $this->getConnection()->select()->from(
                ['cat' => $this->getTable('catalog_category_product_index')],
                'cat.product_id'
            )->where($this->getConnection()->prepareSqlCondition('cat.category_id', ['in' => $values]));
            $selectCondition = [
                $this->mapConditionType($conditionType) => $categorySelect
            ];
            $whereCondition = $this->getConnection()->prepareSqlCondition('e.entity_id', $selectCondition);
            $this->getSelect()->where($whereCondition);
            $this->requestBuilder->bind(Category::ATTRIBUTE_CODE, $values);
        }
        return $this;
    }

    /**
     * Map equal and not equal conditions to in and not in
     *
     * @param string $conditionType
     * @return mixed
     */
    private function mapConditionType($conditionType)
    {
        $conditionsMap = [
            'eq' => 'in',
            'neq' => 'nin'
        ];
        return isset($conditionsMap[$conditionType]) ? $conditionsMap[$conditionType] : $conditionType;
    }
}
