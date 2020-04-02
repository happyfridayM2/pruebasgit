<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\ResourceModel;

/**
 * Class GroupAttrValue
 * @package Amasty\Shopby\Model\ResourceModel
 */
class GroupAttrValue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_amshopby_group_attr_value', 'group_option_id');
    }
}
