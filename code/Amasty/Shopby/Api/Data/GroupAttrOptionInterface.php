<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

namespace Amasty\Shopby\Api\Data;

interface GroupAttrOptionInterface
{
    const ID = 'group_option_id';
    const GROUP_ID = 'group_id';
    const OPTION_ID = 'option_id';
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
     * @return int
     */
    public function getOptionId();

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param $id
     * @return GroupAttrOptionInterface
     */
    public function setId($id);

    /**
     * @param $id
     * @return GroupAttrOptionInterface
     */
    public function setGroupId($id);

    /**
     * @param $option
     * @return GroupAttrOptionInterface
     */
    public function setOptionId($option);

    /**
     * @param $sort
     * @return GroupAttrOptionInterface
     */
    public function setSortOrder($sort);
}
