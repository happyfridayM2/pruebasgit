<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\ResourceModel;

use Amasty\Shopby\Api\GroupRepositoryInterface;

/**
 * Class GroupRepository
 * @package Amasty\Shopby\Model\ResourceModel
 */
class GroupRepository implements GroupRepositoryInterface
{
    /**
     * @var GroupAttr
     */
    protected $groupAttr;

    public function __construct(
        \Amasty\Shopby\Model\ResourceModel\GroupAttr $groupAttr
    ) {
        $this->groupAttr = $groupAttr;
    }

    /**
     * @param $groupCode
     * @return array|false
     */
    public function getGroupOptionsIds($groupCode)
    {
        $select = $this->groupAttr->getConnection()->select()->from(
            ['group' => $this->groupAttr->getTable('amasty_amshopby_group_attr')],
            ''
        )->where('group.group_code = ?', $groupCode);
        $select->joinLeft(
            ['option' => $this->groupAttr->getTable('amasty_amshopby_group_attr_option')],
            'option.group_id=group.group_id',
            ['option.option_id']
        );
        return $this->groupAttr->getConnection()->fetchCol($select);
    }
}
