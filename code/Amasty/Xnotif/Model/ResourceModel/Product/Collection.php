<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Model\ResourceModel\Product;

/**
 * Class Collection
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Customer mode flag
     *
     * @var bool
     */
    private $customerModeFlag = false;

    /**
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        if ($this->getIsCustomerMode()) {
            $this->_renderFilters();

            $unionSelect = clone $this->getSelect();

            $unionSelect->reset(\Zend_Db_Select::COLUMNS);
            $unionSelect->columns('e.entity_id');

            $unionSelect->reset(\Zend_Db_Select::ORDER);
            $unionSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
            $unionSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);

            $countSelect = clone $this->getSelect();
            $countSelect->reset();
            $countSelect->from(['a' => $unionSelect], 'COUNT(*)');
        } else {
            $countSelect = parent::getSelectCountSql();
        }

        return $countSelect;
    }

    /**
     * Get customer mode flag value
     *
     * @return bool
     */
    public function getIsCustomerMode()
    {
        return $this->customerModeFlag;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setIsCustomerMode($value)
    {
        $this->customerModeFlag = (bool)$value;

        return $this;
    }

    /**
     * @return $this
     */
    public function joinPriceTable()
    {
        $stockAlertTable = $this->_resource->getTableName('product_alert_price');
        $this->addAttributeToSelect('name');

        $select = $this->getSelect();
        $select->joinRight(
            ['s' => $stockAlertTable],
            's.product_id = e.entity_id',
            ['cnt' => 'count(s.product_id)', 'last_d' => 'MAX(add_date)', 'first_d' => 'MIN(add_date)', 'product_id']
        )
            ->where('status=0')
            ->group(['s.product_id']);

        $select->columns(
            ['website_id' => new \Zend_Db_Expr("SUBSTRING( GROUP_CONCAT( `s`.`website_id` ) , 1, 100 )")]
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function joinStockTable()
    {
        $stockAlertTable = $this->_resource->getTableName(
            'product_alert_stock'
        );

        $this->addAttributeToSelect('name')
            ->addAttributeToFilter(
                'status',
                ['eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED]
            );

        $select = $this->getSelect();

        $select->joinRight(
            ['s' => $stockAlertTable],
            's.product_id = e.entity_id',
            [
                'total_cnt' => 'count(s.product_id)',
                'cnt' => 'COUNT( NULLIF(`s`.`status`, 1) )',
                'last_d' => 'MAX(add_date)', 'first_d' => 'MIN(add_date)',
                'product_id',
                'parent_id' => 's.parent_id'
            ]
        )
            ->group(['s.product_id']);

        $select->columns(
            ['website_id' => new \Zend_Db_Expr(
                "SUBSTRING( GROUP_CONCAT( `s`.`website_id` ) , 1, 100 )"
            )]
        );

        return $this;
    }

    /**
     * @param string $type
     * @param int $customerId
     * @param string $customerEmail
     */
    public function addSubscriptionData($type, $customerId, $customerEmail)
    {
        $select = $this->getSelect();
        $this->addAttributeToSelect('*');

        $select->joinInner(
            ['s' => $this->getTable('product_alert_' . $type)],
            's.product_id = e.entity_id',
            ['add_date', 'alert_' . $type . '_id', 'parent_id']
        )
            ->where('customer_id=? OR email=?', $customerId, $customerEmail)
            ->group(['s.product_id']);

        $this->setFlag('has_stock_status_filter', true);
    }
}
