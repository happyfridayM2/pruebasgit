<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


declare(strict_types=1);

namespace Amasty\Shopby\Plugin\Framework\Search\Adapter\Mysql;

use Amasty\Shopby\Model\Inventory\Resolver as InventoryResolver;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockResource;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Search\Adapter\Mysql\Mapper;
use Magento\Framework\Search\RequestInterface;
use Zend_Db_Select_Exception;

class MapperPlugin
{
    /**
     * @var StockResource
     */
    private $stockResource;

    /**
     * @var InventoryResolver
     */
    private $inventoryResolver;

    public function __construct(InventoryResolver $inventoryResolver, StockResource $stockResource)
    {
        $this->stockResource = $stockResource;
        $this->inventoryResolver = $inventoryResolver;
    }

    /**
     * @param Mapper $subject
     * @param Select $select
     * @param RequestInterface $request
     * @return Select
     * @throws Zend_Db_Select_Exception
     * @throws LocalizedException
     */
    public function afterBuildQuery(Mapper $subject, Select $select, RequestInterface $request): Select
    {
        $subSelect = $select->getPart(Select::FROM)['main_select']['tableName'] ?? null;
        if ($subSelect && $subSelect instanceof Select) {
            $this->checkAndModify($subSelect);
        }

        return $select;
    }

    /**
     * @param Select $select
     * @throws Zend_Db_Select_Exception
     * @throws LocalizedException
     */
    protected function checkAndModify(Select $select)
    {
        $fromTables = $select->getPart(Select::FROM);
        if (isset($fromTables['stock_status_filter'])) {
            if ($fromTables['stock_status_filter']['tableName'] == $this->stockResource->getMainTable()) {
                $this->adaptForCatalogInventory($select);
            } else {
                $this->adaptForMsi($select);
            }
        }
    }

    /**
     * @param Select $select
     * @throws Zend_Db_Select_Exception
     */
    private function adaptForCatalogInventory(Select $select)
    {
        $fromTables = $select->getPart(Select::FROM);
        $fromTables['stock_status_filter']['joinCondition'] = $this->inventoryResolver->replaceWebsiteWithDefault(
            $fromTables['stock_status_filter']['joinCondition']
        );
        $select->setPart(Select::FROM, $fromTables);
    }

    /**
     * @param Select $select
     * @throws Zend_Db_Select_Exception
     */
    private function adaptForMsi(Select $select)
    {
        $whereParts = $select->getPart(Select::WHERE);
        foreach ($whereParts as &$wherePart) {
            if (strpos($wherePart, 'stock_status_filter') !== false) {
                $wherePart = str_replace(
                    '`stock_status_filter`.`stock_status`',
                    '`stock_status_filter`.`is_salable`',
                    $wherePart
                );
                break;
            }
        }
        $select->setPart(Select::WHERE, $whereParts);
    }
}
