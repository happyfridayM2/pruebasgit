<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */

namespace Amasty\Xnotif\Model\ResourceModel\Product\Link\Product;

/**
 * Class Collection
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
{
    /**
     * @param bool|false $printQuery
     * @param bool|false $logQuery
     * @return $this
     * @throws \Zend_Db_Select_Exception
     */
    public function load($printQuery = false, $logQuery = false)
    {
        /*remove in stock filter*/
        $select = $this->getSelect();
        $where = $select->getPart('where');
        foreach ($where as $i => $item) {
            if (strpos($item, 'stock_status') !== false) {
                unset($where[$i]);
            }
        }
        $select->setPart('where', $where);

        $from = $select->getPart('from');
        if (array_key_exists('at_inventory_in_stock', $from)
        ) {
            $from['at_inventory_in_stock']['joinCondition'] =
                str_replace(
                    'AND at_inventory_in_stock.is_in_stock=1',
                    '',
                    $from['at_inventory_in_stock']['joinCondition']
                );
        }
        $select->setPart('from', $from);

        return parent::load($printQuery, $logQuery);
    }
}
