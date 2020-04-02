<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\ResourceModel\GroupAttrValue;

/**
 * Class Collection
 * @package Amasty\Shopby\Model\ResourceModel\GroupAttrValue
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'group_option_id';

    protected function _construct()
    {
        $this->_init(
            \Amasty\Shopby\Model\GroupAttrValue::class,
            \Amasty\Shopby\Model\ResourceModel\GroupAttrValue::class
        );
    }
}
