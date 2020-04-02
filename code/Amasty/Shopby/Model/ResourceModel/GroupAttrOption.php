<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\ResourceModel;

/**
 * Class GroupAttrOption
 * @package Amasty\Shopby\Model\ResourceModel
 */
class GroupAttrOption extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_amshopby_group_attr_option', 'group_option_id');
    }
}
