<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Model\ResourceModel\Bundle\Selection;

/**
 * Class Collection
 */
class Collection extends \Magento\Bundle\Model\ResourceModel\Selection\Collection
{
    /**
     * @return $this|\Magento\Bundle\Model\ResourceModel\Selection\Collection
     */
    public function _afterLoad()
    {
        parent::_afterLoad();

        if ($this->getStoreId() && $this->_items) {
            foreach ($this->_items as $item) {
                $item->setStoreId($this->getStoreId());

                /*show out of stock bundle options*/
                if (!$item->getData('is_salable')) {
                    $item->setData('is_salable', 1);
                    $item->setData('amasty_native_is_salable', 0);

                    $name = $item->getName();
                    $name .= ' (' . __('Out of Stock') . ')';
                    $item->setData('name', $name);
                } else {
                    $item->setData('amasty_native_is_salable', 1);
                }
            }
        }
        return $this;
    }

    /**
     * @param bool $printQuery
     * @param bool $logQuery
     *
     * @return $this|\Magento\Bundle\Model\ResourceModel\Selection\Collection
     */
    public function load($printQuery = false, $logQuery = false)
    {
        /*remove in stock filter*/
        $select = $this->getSelect();
        $where = $select->getPart('where');
        foreach ($where as $i => $item) {
            if (strpos($item, 'stock_status_index.stock_status = 1') !== false) {
                unset($where[$i]);
            }
        }
        $select->setPart('where', $where);

        parent::load($printQuery, $logQuery);

        return $this;
    }
}
