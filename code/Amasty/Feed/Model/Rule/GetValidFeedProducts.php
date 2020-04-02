<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Rule;

use Amasty\Feed\Model\Rule\RuleFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Rule\Model\Condition\Sql\Builder;
use Amasty\Feed\Model\ValidProduct\ResourceModel\ValidProduct;

class GetValidFeedProducts
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var array
     */
    private $productIds = [];

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var Builder
     */
    protected $sqlBuilder;

    public function __construct(
        RuleFactory $ruleFactory,
        CollectionFactory $productCollectionFactory,
        Builder $sqlBuilder
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->ruleFactory = $ruleFactory;
        $this->sqlBuilder = $sqlBuilder;
    }

    /**
     * @param \Amasty\Feed\Model\Feed $model
     * @param array $ids
     *
     * @return array
     */
    public function execute(\Amasty\Feed\Model\Feed $model, array $ids = [])
    {
        $rule = $this->ruleFactory->create();
        $rule->setConditionsSerialized($model->getConditionsSerialized());
        $rule->setStoreId($model->getStoreId());
        $model->setRule($rule);
        $this->updateIndex($model, $ids);
    }

    public function updateIndex(\Amasty\Feed\Model\Feed $model, array $ids = [])
    {
        /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productCollection = $this->prepareCollection($model, $ids);
        $this->productIds = [];

        $conditions = $model->getRule()->getConditions();
        $conditions->collectValidatedAttributes($productCollection);
        $this->sqlBuilder->attachConditionToCollection($productCollection, $conditions);
        /**
         * Prevent retrieval of duplicate records. This may occur when multiselect product attribute matches
         * several allowed values from condition simultaneously
         */
        $productCollection->distinct(true);
        $productCollection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select = $productCollection->getSelect()->columns(
            [
                'entity_id' => new \Zend_Db_Expr('null'),
                'feed_id' => new \Zend_Db_Expr($model->getEntityId()),
                'valid_product_id' => 'e.' . $productCollection->getEntity()->getIdFieldName()
            ]
        );
        $query = $select->insertFromSelect($productCollection->getResource()->getTable(ValidProduct::TABLE_NAME));
        $productCollection->getConnection()->query($query);
    }

    /**
     * @param \Amasty\Feed\Model\Feed $model
     * @param array $ids
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function prepareCollection(\Amasty\Feed\Model\Feed $model, $ids = [])
    {
        /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addStoreFilter($model->getStoreId());

        if ($ids) {
            $productCollection->addAttributeToFilter('entity_id', ['in' => $ids]);
        }

        // DBEST-1250
        if ($model->getExcludeDisabled()) {
            $productCollection->addAttributeToFilter('status', ['eq' => Status::STATUS_ENABLED]);
        }
        if ($model->getExcludeNotVisible()) {
            $productCollection->addAttributeToFilter('visibility', ['neq' => Visibility::VISIBILITY_NOT_VISIBLE]);
        }
        if ($model->getExcludeOutOfStock()) {
            $productCollection->getSelect()->joinInner(
                ['s' => $productCollection->getTable('cataloginventory_stock_item')],
                $productCollection->getSelect()->getConnection()->quoteInto(
                    's.product_id = e.entity_id AND s.is_in_stock = ?',
                    1,
                    \Zend_Db::INT_TYPE
                ),
                'is_in_stock'
            );
        }

        $model->getRule()->getConditions()->collectValidatedAttributes($productCollection);

        return $productCollection;
    }
}
