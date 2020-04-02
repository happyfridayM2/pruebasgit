<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Indexer\Price;

use Magento\Framework\App\ResourceConnection;

/**
 * Class DefaultPrice
 * @package Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Indexer\Price
 */
class DefaultPrice
{
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

    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Framework\Stdlib\DateTime $date,
        \Magento\Catalog\Model\ResourceModel\Product $productResource
    ) {
        $this->resource = $resourceConnection;
        $this->date = $date;
        $this->productIdLink = $productResource->getLinkField();
    }

    /**
     * @param $subject
     * @param $entityIds
     * @return array
     */
    public function beforeReindexEntity($subject, $entityIds)
    {
        $this->entityIds = $entityIds;
        $this->subject = $subject;
        return [$entityIds];
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterReindexAll($subject, $result)
    {
        $this->subject = $subject;
        return $this->afterReindexEntity($subject, $result);
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterReindexEntity($subject, $result)
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
    public function getIdxTable()
    {
        return $this->subject->getIdxTable();
    }
}
