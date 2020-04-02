<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

namespace Amasty\Shopby\Api\Data;

interface GroupAttrValueInterface
{
    const ID = 'group_option_id';
    const GROUP_ID = 'group_id';
    const VALUE = 'value';
    const SORT_ORDER = 'sort_order';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getGroupId();

    /**
     * @return string
     */
    public function getValue();

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param $id
     * @return GroupAttrValueInterface
     */
    public function setId($id);

    /**
     * @param $id
     * @return GroupAttrValueInterface
     */
    public function setGroupId($id);

    /**
     * @param $value
     * @return GroupAttrValueInterface
     */
    public function setValue($value);

    /**
     * @param $sort
     * @return GroupAttrValueInterface
     */
    public function setSortOrder($sort);
}
