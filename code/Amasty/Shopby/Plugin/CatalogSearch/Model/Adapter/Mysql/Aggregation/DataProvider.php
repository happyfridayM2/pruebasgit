<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\CatalogSearch\Model\Adapter\Mysql\Aggregation;

use Amasty\Shopby\Model\Inventory\Resolver as InventoryResolver;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\Store;
use Magento\CatalogInventory\Api\StockConfigurationInterface as StockConfigurationInterface;

class DataProvider
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $catalogProductVisibility;

    /**
     * @var \Amasty\Shopby\Model\Layer\Filter\IsNew\Helper
     */
    private $isNewHelper;

    /**
     * @var \Amasty\Shopby\Model\Layer\Filter\OnSale\Helper
     */
    protected $onSaleHelper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
     */
    private $stockResource;

    /**
     * @var InventoryResolver
     */
    private $inventoryResolver;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    public function __construct(
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Amasty\Shopby\Model\Layer\Filter\IsNew\Helper $isNewHelper,
        \Amasty\Shopby\Model\Layer\Filter\OnSale\Helper $onSaleHelper,
        \Magento\Eav\Model\Config $eavConfig,
        ScopeConfigInterface $scopeConfig,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $stockResource,
        InventoryResolver $inventoryResolver,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->isNewHelper = $isNewHelper;
        $this->onSaleHelper = $onSaleHelper;
        $this->scopeConfig = $scopeConfig;
        $this->eavConfig = $eavConfig;
        $this->stockResource = $stockResource;
        $this->inventoryResolver = $inventoryResolver;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * @param \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $subject
     * @param \Closure $proceed
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param Table $entityIdsTable
     * @return \Magento\Framework\DB\Select|mixed
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundGetDataSet(
        \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $subject,
        \Closure $proceed,
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {
        if ($bucket->getField() == 'stock_status') {
            $isStockEnabled = $this->scopeConfig->isSetFlag(
                'amshopby/stock_filter/enabled',
                ScopeInterface::SCOPE_STORE
            );
            if ($isStockEnabled) {
                return $this->addStockAggregation($entityIdsTable);
            }
        }

        if ($bucket->getField() == 'rating_summary') {
            $isRatingEnabled = $this->scopeConfig->isSetFlag(
                'amshopby/rating_filter/enabled',
                ScopeInterface::SCOPE_STORE
            );
            if ($isRatingEnabled) {
                return $this->addRatingAggregation($entityIdsTable, $dimensions);
            }
        }

        if ($bucket->getField() == 'am_is_new') {
            $isNewEnabled = $this->scopeConfig->isSetFlag(
                'amshopby/am_is_new_filter/enabled',
                ScopeInterface::SCOPE_STORE
            );
            if ($isNewEnabled) {
                return $this->addIsNewAggregation($entityIdsTable, $dimensions);
            }
        }

        if ($bucket->getField() == 'am_on_sale') {
            $isOnSaleEnabled = $this->scopeConfig->isSetFlag(
                'amshopby/am_on_sale_filter/enabled',
                ScopeInterface::SCOPE_STORE
            );
            if ($isOnSaleEnabled) {
                return $this->addOnSaleAggregation($entityIdsTable, $dimensions);
            }
        }

        return $proceed($bucket, $dimensions, $entityIdsTable);
    }

    /**
     * @return bool
     */
    private function isStockSourceQty()
    {
        $stockSource = $this->scopeConfig
            ->getValue('amshopby/stock_filter/stock_source', ScopeInterface::SCOPE_STORE);
        return $stockSource === \Amasty\Shopby\Model\Source\StockFilterSource::QTY;
    }

    /**
     * @param Table $entityIdsTable
     * @return \Magento\Framework\DB\Select
     */
    private function addStockAggregation(Table $entityIdsTable)
    {
        $derivedTable = $this->resource->getConnection()->select();
        if ($this->isStockSourceQty()) {
            $storeId = $this->scopeResolver->getScope()->getId();
            $qty = (float)$this->stockConfiguration->getMinQty($storeId);
            $cond = "type_id != 'simple' OR qty > IF(use_config_min_qty, $qty, min_qty)";
            $derivedTable->from(
                ['e' => $this->resource->getTableName('catalog_product_entity')]
            )->joinInner(
                ['entities' => $entityIdsTable->getName()],
                'e.entity_id  = entities.entity_id',
                []
            )->joinLeft(
                ['at_qty' => $this->resource->getTableName('pref_cataloginventory_stock_item')],
                'at_qty.product_id = e.entity_id AND at_qty.stock_id = 1',
                ['value' => new \Zend_Db_Expr("IF($cond, 1, 0)")]
            );
        } else {
            $derivedTable->from(
                ['e' => $this->resource->getTableName('catalog_product_entity')]
            )->joinInner(
                ['entities' => $entityIdsTable->getName()],
                'e.entity_id  = entities.entity_id',
                []
            );

            $this->stockResource->addStockStatusToSelect($derivedTable, $this->scopeResolver->getScope()->getWebsite());

            $catalogInventoryTable = $this->stockResource->getMainTable();
            $fromTables = $derivedTable->getPart(Select::FROM);
            if ($this->inventoryResolver->isMsiEnabled()
                && $fromTables['stock_status']['tableName'] != $catalogInventoryTable
            ) {
                $stockStatusColumn = 'is_salable';
            } else {
                $stockStatusColumn = 'stock_status';
                $fromTables['stock_status']['joinCondition'] = $this->inventoryResolver->replaceWebsiteWithDefault(
                    $fromTables['stock_status']['joinCondition']
                );
                $derivedTable->setPart(Select::FROM, $fromTables);
            }

            $derivedTable->columns(['value' => 'stock_status.' . $stockStatusColumn]);
        }

        $select = $this->resource->getConnection()->select();
        $select->from(['main_table' => $derivedTable]);
        return $select;
    }

    /**
     * @param Table $entityIdsTable
     * @param array $dimensions
     * @return \Magento\Framework\DB\Select
     */
    private function addRatingAggregation(
        Table $entityIdsTable,
        $dimensions
    ) {
        $currentScope = $dimensions['scope']->getValue();
        $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();
        $derivedTable = $this->resource->getConnection()->select();
        $derivedTable->from(
            ['entities' => $entityIdsTable->getName()],
            []
        );

        $columnRating = new \Zend_Db_Expr("
                IF(main_table.rating_summary >=100,
                    5,
                    IF(
                        main_table.rating_summary >=80,
                        4,
                        IF(main_table.rating_summary >=60,
                            3,
                            IF(main_table.rating_summary >=40,
                                2,
                                IF(main_table.rating_summary >=20,
                                    1,
                                    0
                                )
                            )
                        )
                    )
                )
            ");

        $derivedTable->joinLeft(
            ['main_table' => $this->resource->getTableName('review_entity_summary')],
            sprintf(
                '`main_table`.`entity_pk_value`=`entities`.entity_id
                AND `main_table`.entity_type = 1
                AND `main_table`.store_id  =  %d',
                $currentScopeId
            ),
            [
                //'entity_id' => 'entity_pk_value',
                'value' => $columnRating,
            ]
        );
        $select = $this->resource->getConnection()->select();
        $select->from(['main_table' => $derivedTable]);
        return $select;
    }

    /**
     * @param Table $entityIdsTable
     * @param array $dimensions
     * @return \Magento\Framework\DB\Select
     */
    private function addIsNewAggregation(
        Table $entityIdsTable,
        $dimensions
    ) {
        $currentScope = $dimensions['scope']->getValue();
        $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();

        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        $collection->addStoreFilter($currentScopeId);
        $this->isNewHelper->addNewFilter($collection);

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('e.entity_id');

        $derivedTable = $this->resource->getConnection()->select();
        $derivedTable->from(
            ['entities' => $entityIdsTable->getName()],
            []
        );

        $derivedTable->joinLeft(
            ['am_is_new' => $collection->getSelect()],
            'am_is_new.entity_id  = entities.entity_id',
            [
                'value' => new \Zend_Db_Expr("if(am_is_new.entity_id is null, 0, 1)")
            ]
        );

        $select = $this->resource->getConnection()->select();
        $select->from(['main_table' => $derivedTable]);

        return $select;
    }

    /**
     * @param Table $entityIdsTable
     * @param array $dimensions
     * @return \Magento\Framework\DB\Select
     */
    private function addOnSaleAggregation(
        Table $entityIdsTable,
        $dimensions
    ) {
        $currentScope = $dimensions['scope']->getValue();
        $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();

        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        $collection->addStoreFilter($currentScopeId);
        $this->onSaleHelper->addOnSaleFilter($collection);

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('e.entity_id');

        $derivedTable = $this->resource->getConnection()->select();
        $derivedTable->from(
            ['entities' => $entityIdsTable->getName()],
            []
        );

        $derivedTable->joinLeft(
            ['am_on_sale' => $collection->getSelect()],
            'am_on_sale.entity_id  = entities.entity_id',
            [
                'value' => new \Zend_Db_Expr("if(am_on_sale.entity_id is null, 0, 1)")
            ]
        );

        $derivedTable->group('entities.entity_id');

        $select = $this->resource->getConnection()->select();
        $select->from(['main_table' => $derivedTable]);

        return $select;
    }
}
