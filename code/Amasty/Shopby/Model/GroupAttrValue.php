<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model;

use Amasty\Shopby\Api\Data\GroupAttrValueInterface;

/**
 * Class GroupAttrValue
 * @package Amasty\Shopby\Model
 */
class GroupAttrValue extends \Magento\Framework\Model\AbstractModel implements GroupAttrValueInterface
{

    protected function _construct()
    {
        $this->_init(\Amasty\Shopby\Model\ResourceModel\GroupAttrValue::class);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->getData(self::GROUP_ID);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * @param $id
     * @return GroupAttrValueInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @param $id
     * @return GroupAttrValueInterface
     */
    public function setGroupId($id)
    {
        return $this->setData(self::GROUP_ID, $id);
    }

    /**
     * @param $option
     * @return GroupAttrValueInterface
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * @param $sort
     * @return GroupAttrValueInterface
     */
    public function setSortOrder($sort)
    {
        return $this->setData(self::SORT_ORDER, $sort);
    }
}
