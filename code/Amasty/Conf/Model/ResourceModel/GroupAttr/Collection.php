<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\ResourceModel\GroupAttr;

use Amasty\Conf\Model\ResourceModel\GroupAttr;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'group_id';

    protected function _construct()
    {
        $this->_init(\Amasty\Conf\Model\GroupAttr::class, \Amasty\Conf\Model\ResourceModel\GroupAttr::class);
    }

    /**
     * @param int $attributeId
     *
     * @return $this
     */
    public function getGroupsByAttributeId($attributeId)
    {
        $this->addFieldToFilter('attribute_id', (int)$attributeId);
        $this->addFieldToFilter('enabled', GroupAttr::IS_ENABLED);

        return $this;
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
        $this->joinField('aagao', 'amasty_conf_group_attr_option', ['option_id'], 'group_id=main_table.group_id');

        return $this;
    }
}
