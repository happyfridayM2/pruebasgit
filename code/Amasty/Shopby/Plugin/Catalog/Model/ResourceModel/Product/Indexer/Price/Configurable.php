<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Indexer\Price;

/**
 * Class Configurable
 * @package Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Indexer\Price
 */
class Configurable extends DefaultPrice
{
    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterReindexEntity($subject, $result)
    {
        $this->addSpecialPriceToConfigurable();

        return $result;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterReindexAll($subject, $result)
    {
        $this->subject = $subject;
        $this->addSpecialPriceToConfigurable();

        return $result;
    }

    /**
     * Perform setting special and final prices for composite products
     *
     * @return void
     */
    private function addSpecialPriceToConfigurable()
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            ['main_table' => $this->getIdxTable()]
        );

        if ($this->productIdLink == 'row_id') {
            $select->joinInner(
                ['simple_link' => $this->resource->getTableName('catalog_product_super_link')],
                'simple_link.product_id = main_table.entity_id',
                []
            );

            $select->joinInner(
                ['product_link' => $this->resource->getTableName('catalog_product_entity')],
                'product_link.row_id = simple_link.parent_id',
                ['parent_id' => 'product_link.row_id']
            );

            if ($this->entityIds) {
                $select->where('product_link.row_id IN (?)', $this->entityIds);
            }
            $select->where('main_table.price > main_table.final_price and main_table.final_price > 0');
            $select->group(['product_link.row_id', 'main_table.customer_group_id', 'main_table.website_id']);

        } else {
            $select->joinInner(
                ['simple_link' => $this->resource->getTableName('catalog_product_super_link')],
                'simple_link.product_id = main_table.entity_id',
                ['parent_id' => 'simple_link.parent_id']
            );
            if ($this->entityIds) {
                $select->where('simple_link.parent_id IN (?)', $this->entityIds);
            }
            $select->where('main_table.price > main_table.final_price and main_table.final_price > 0');
            $select->group(['simple_link.parent_id', 'main_table.customer_group_id', 'main_table.website_id']);
        }

        $insertData = $connection->fetchAll($select);
        if (!empty($insertData)) {
            foreach ($insertData as &$row) {
                if (isset($row['parent_id'])) {
                    $row['entity_id'] = $row['parent_id'];
                    unset($row['parent_id']);
                }
            }

            $connection->insertOnDuplicate(
                $this->getIdxTable(),
                $insertData,
                ['price', 'final_price']
            );
        }
    }
}
