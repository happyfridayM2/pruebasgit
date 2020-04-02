<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\ResourceModel\GroupAttr;

/**
 * Class Collection
 * @package Amasty\Shopby\Model\ResourceModel\GroupAttr
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'group_id';

    protected function _construct()
    {
        $this->_init(\Amasty\Shopby\Model\GroupAttr::class, \Amasty\Shopby\Model\ResourceModel\GroupAttr::class);
    }

    /**
     * @param $name
     * @param $table
     * @param $field
     * @param $where
     * @return $this
     */
    public function joinField($name, $table, $field, $where)
    {
        $this->getSelect()->joinLeft(
            [$name => $this->getTable($table)],
            $name . "." . $where,
            $field
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function joinOptions()
    {
        $this->joinField('aagao', 'amasty_amshopby_group_attr_option', ['option_id'], 'group_id=main_table.group_id');

        return $this;
    }

    /**
     * @return $this
     */
    public function joinValues()
    {
        $this->joinField('aagav', 'amasty_amshopby_group_attr_value', ['value'], 'group_id=main_table.group_id');

        return $this;
    }
}
