<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Indexer\Price\Dimensional;

/**
 * Class Configurable
 * @package Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Indexer\Price\Dimensional
 */
class Configurable extends Simple
{
    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterExecuteByDimensions($subject, $result)
    {
        $this->addSpecialPriceToConfigurable();

        return $result;
    }

    /**
     * @return void
     */
    private function addSpecialPriceToConfigurable()
    {
        if (!$this->entityIds) {
            return;
        }

        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(['main_table' => $this->getDataTable()]);

        $select->joinInner(
            ['simple_link' => $this->resource->getTableName('catalog_product_super_link')],
            'simple_link.product_id=main_table.entity_id',
            []
        );
        if ($this->productIdLink == 'row_id') {
            $select->joinInner(
                ['product_link' => $this->resource->getTableName('catalog_product_entity')],
                'simple_link.parent_id=product_link.row_id',
                ['parent_id' => 'product_link.entity_id']
            );
        } else {
            $select->columns(['parent_id' => 'simple_link.parent_id']);
        }

        $select->where('simple_link.parent_id IN (?)', $this->entityIds);
        $select->where('main_table.price > main_table.final_price and main_table.final_price > 0');

        $select->group(['simple_link.parent_id', 'main_table.customer_group_id', 'main_table.website_id']);

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
