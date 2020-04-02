<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\CatalogSearch\Model\Search;

use Amasty\Shopby\Plugin\CatalogSearch\Model\Search\FilterMapper\CustomExclusionStrategy;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Filter\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\App\ResourceConnection;

class IndexBuilder
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Amasty\Shopby\Model\Request
     */
    protected $shopbyRequest;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var \Amasty\Shopby\Model\Layer\Cms\Manager
     */
    protected $cmsManager;

    /**
     * @var CustomExclusionStrategy
     */
    private $customExclusionStrategy;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
     */
    private $stockResource;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Shopby\Model\Request $shopbyRequest,
        ResourceConnection $resource,
        \Amasty\Shopby\Model\Layer\Cms\Manager $cmsManager,
        CustomExclusionStrategy $customExclusionStrategy,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $stockResource
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->shopbyRequest = $shopbyRequest;
        $this->resource = $resource;
        $this->cmsManager = $cmsManager;
        $this->customExclusionStrategy = $customExclusionStrategy;
        $this->stockResource = $stockResource;
    }

    /**
     * Build index query
     *
     * @param $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundBuild($subject, callable $proceed, RequestInterface $request)
    {
        $select = $proceed($request);
        $filters = $this->getFilters($request->getQuery());
        foreach ($filters as $filter) {
            $this->customExclusionStrategy->apply($filter, $select);
        }

        if ($this->isEnabledShowOutOfStock() && $this->isEnabledStockFilter()) {
            if ($this->shopbyRequest->getParam('stock')) {
                $this->addStockDataToSelect($select);
            }
        }

        if ($this->cmsManager->isCmsPageNavigation()) {
            $this->cmsManager->addCmsPageDataToSelect($select);
        }

        return $select;
    }

    /**
     * @param RequestQueryInterface $query
     * @return FilterInterface[]
     */
    private function getFilters($query)
    {
        $filters = [];
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_BOOL:
                /** @var \Magento\Framework\Search\Request\Query\BoolExpression $query */
                foreach ($query->getMust() as $subQuery) {
                    // @codingStandardsIgnoreLine
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                foreach ($query->getShould() as $subQuery) {
                    // @codingStandardsIgnoreLine
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                foreach ($query->getMustNot() as $subQuery) {
                    // @codingStandardsIgnoreLine
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                break;
            case RequestQueryInterface::TYPE_FILTER:
                /** @var Filter $query */
                $filter = $query->getReference();
                if (FilterInterface::TYPE_BOOL === $filter->getType()) {
                    $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
                } else {
                    $filters[] = $filter;
                }
                break;
            default:
                break;
        }
        return $filters;
    }

    /**
     * @param BoolExpression $boolExpression
     * @return FilterInterface[]
     */
    private function getFiltersFromBoolFilter(BoolExpression $boolExpression)
    {
        $filters = [];
        /** @var BoolExpression $filter */
        foreach ($boolExpression->getMust() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                // @codingStandardsIgnoreLine
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getShould() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                // @codingStandardsIgnoreLine
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getMustNot() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                // @codingStandardsIgnoreLine
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        return $filters;
    }

    /**
     * @param Select $select
     */
    protected function addStockDataToSelect(Select $select)
    {
        $select->joinInner(
            ['e' => $this->resource->getTableName('catalog_product_entity')],
            'search_index.entity_id = e.entity_id',
            []
        );

        $this->stockResource->addStockStatusToSelect($select, $this->storeManager->getStore()->getWebsite());

        $this->renameStockTable($select);
    }

    /**
     * @return bool
     */
    protected function isEnabledShowOutOfStock()
    {
        return $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    protected function isEnabledStockFilter()
    {
        return $this->scopeConfig->isSetFlag(
            'amshopby/stock_filter/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param Select $select
     */
    private function renameStockTable($select)
    {
        // remove unused alias
        $columns = $select->getPart(Select::COLUMNS);
        array_pop($columns);
        $select->setPart(Select::COLUMNS, $columns);
        // rename stock table in stock_status_filter
        $from = $select->getPart(Select::FROM);
        $stockStatus = $from['stock_status'];
        unset($from['stock_status']);
        $stockStatus['joinCondition'] = str_replace(
            'stock_status',
            'stock_status_filter',
            $stockStatus['joinCondition']
        );
        $from['stock_status_filter'] = $stockStatus;
        $select->setPart(Select::FROM, $from);
    }
}
