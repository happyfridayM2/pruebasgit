<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Indexer\Price\Dimensional;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\IndexScopeResolverInterface as TableResolver;

/**
 * Class Simple
 * @package Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Indexer\Price\Dimensional
 */
class Simple
{
    const MAIN_INDEX_TABLE = 'catalog_product_index_price';

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $date;

    /**
     * @var array
     */
    protected $entityIds;

    /**
     * @var string
     */
    protected $productIdLink;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
     */
    protected $subject;

    /**
     * @var array
     */
    protected $dimensions;

    /**
     * @var string
     */
    protected $tmpTableSuffix = '_temp';

    /**
     * @var TableResolver
     */
    protected $tableResolver;

    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Framework\Stdlib\DateTime $date,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        TableResolver $tableResolver
    ) {
        $this->resource = $resourceConnection;
        $this->date = $date;
        $this->productIdLink = $productResource->getLinkField();
        $this->tableResolver = $tableResolver;
    }

    /**
     * @param $subject
     * @param $entityIds
     * @return array
     */
    public function beforeExecuteByDimensions($subject, array $dimensions, \Traversable $entityIds)
    {
        $this->subject = $subject;
        $this->dimensions = $dimensions;
        $this->entityIds = iterator_to_array($entityIds);
        return [$dimensions, $entityIds];
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterExecuteByDimensions($subject, $result)
    {
        $columns = [
            'entity_id' => 'main_table.entity_id',
            'customer_group_id' => 'main_table.customer_group_id',
            'website_id' => 'main_table.website_id',
            'tax_class_id' => 'main_table.tax_class_id',
            'price' => 'main_table.price',
            'final_price' => new \Zend_Db_Expr('LEAST(main_table.final_price, rule_index.rule_price)'),
            'min_price' => new \Zend_Db_Expr('LEAST(main_table.min_price, rule_index.rule_price)'),
            'max_price' => new \Zend_Db_Expr('LEAST(main_table.max_price, rule_index.rule_price)'),
            'tier_price' => 'main_table.tier_price',
        ];

        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            ['main_table' => $this->getIdxTable()],
            $columns
        );
        $conditions = [
            'rule_index.product_id = main_table.entity_id',
            'rule_index.website_id = main_table.website_id',
            'rule_index.customer_group_id = main_table.customer_group_id'

        ];
        $select->joinInner(
            ['rule_index' => $this->resource->getTableName('catalogrule_product_price')],
            implode(' AND ', $conditions),
            []
        );
        $now = new \DateTime();
        $select->where('rule_index.rule_date = ?', $this->date->formatDate($now, false));
        if ($this->entityIds) {
            $select->where('main_table.entity_id IN (?)', $this->entityIds);
        }

        $insertData = $connection->fetchAll($select);
        if (!empty($insertData)) {
            $connection->insertOnDuplicate(
                $this->getIdxTable(),
                $insertData,
                ['final_price', 'min_price', 'max_price']
            );

        }

        return $result;
    }

    /**
     * @return string
     */
    public function getDataTable()
    {
        return $this->tableResolver->resolve(self::MAIN_INDEX_TABLE, $this->dimensions);
    }

    /**
     * @return string
     */
    public function getIdxTable()
    {
        return $this->tableResolver->resolve(self::MAIN_INDEX_TABLE, $this->dimensions) . $this->tmpTableSuffix;
    }
}
