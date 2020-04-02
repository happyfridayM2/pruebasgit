<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model\ResourceModel\AdminNotification;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * Class Collection
 */
class Collection extends ProductCollection
{
    const STOCK_ALERT_TABLE = 'product_alert_stock';

    public function getCollection()
    {
        $this->addAttributeToSelect('name');
        $alertTable = $this->_resource->getTableName(self::STOCK_ALERT_TABLE);
        $this->getSelect()->joinRight(
            ['s' => $alertTable],
            's.product_id = e.entity_id',
            [
                'total_cnt' => 'count(s.product_id)',
                'cnt' => 'COUNT( NULLIF(`s`.`status`, 1) )',
                'last_d' => 'MAX(add_date)',
                'product_id'
            ]
        )
            ->where('DATE(add_date) = DATE(NOW())')
            ->group(['s.product_id']);

        return $this;
    }
}
