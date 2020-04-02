<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model\ResourceModel\Stock;

/**
 * Class Collection
 */
class Collection extends \Magento\ProductAlert\Model\ResourceModel\Stock\Collection
{
    /**
     * @return $this
     */
    public function joinAdditionalTables()
    {
        $select = $this->getSelect();
        $entityTable = $this->getTable('catalog_product_entity');
        $customerTable = $this->getTable('customer_entity');

        $select->joinInner(
            ['ent' => $entityTable],
            'main_table.product_id = ent.entity_id',
            [
                'first_d' => 'main_table.add_date',
                'sku'
            ]
        );

        $select->joinLeft(
            ['cust' => $customerTable],
            'main_table.customer_id = cust.entity_id',
            [
                'final_email' => 'CONCAT(COALESCE(`main_table`.`email`,""), COALESCE(`cust`.`email`,""))'
            ]
        );

        return $this;
    }
}
