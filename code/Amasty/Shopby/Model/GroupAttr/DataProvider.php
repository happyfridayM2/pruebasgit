<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\GroupAttr;

use \Amasty\Shopby\Api\Data\GroupAttrInterface;

/**
 * Class DataProvider
 * @package Amasty\Shopby\Model\GroupAttr
 */
class DataProvider
{
    const ENABLED = 1;

    /**
     * @var \Amasty\Shopby\Model\ResourceModel\GroupAttr\Collection
     */
    private $groupAttributeCollection;

    /**
     * @var \Amasty\Shopby\Model\ResourceModel\GroupAttrOption\Collection
     */
    private $groupAttributeOptionCollection;

    /**
     * @var \Amasty\Shopby\Model\ResourceModel\GroupAttrValue\Collection
     */
    private $groupAttributeValueCollection;

    /**
     * @var GroupAttrInterface[][]
     */
    private $groupByAttributeId = [];

    public function __construct(
        \Amasty\Shopby\Model\ResourceModel\GroupAttr\CollectionFactory $groupAttributeCollectionFactory,
        \Amasty\Shopby\Model\ResourceModel\GroupAttrOption\CollectionFactory $groupAttributeOptionCollectionFactory,
        \Amasty\Shopby\Model\ResourceModel\GroupAttrValue\CollectionFactory $groupAttributeValueCollectionFactory
    ) {
        $this->groupAttributeCollection = $groupAttributeCollectionFactory->create();
        $this->groupAttributeOptionCollection = $groupAttributeOptionCollectionFactory->create();
        $this->groupAttributeValueCollection = $groupAttributeValueCollectionFactory->create();
        $this->initGroups();
    }

    /**
     * @return $this
     */
    private function initGroups()
    {
        $groupCollection = $this->groupAttributeCollection->addFieldToFilter('enabled', self::ENABLED)
            ->addOrder('position', \Magento\Framework\Data\Collection\AbstractDb::SORT_ORDER_ASC);
        foreach ($groupCollection as $item) {
            $this->groupByAttributeId[$item->getAttributeId()][] = $item;
        }

        foreach ($this->groupAttributeOptionCollection as $option) {
            $item = $groupCollection->getItemById($option->getGroupId());
            if ($item !== null) {
                $item->addOption($option);
            }
        }

        foreach ($this->groupAttributeValueCollection as $value) {
            $item = $groupCollection->getItemById($value->getGroupId());
            if ($item !== null) {
                $item->addValue($value);
            }
        }

        return $this;
    }

    /**
     * @param int $attributeId
     * @return GroupAttrInterface[]
     */
    public function getGroupsByAttributeId($attributeId)
    {
        return isset($this->groupByAttributeId[$attributeId])
            ? $this->groupByAttributeId[$attributeId] : [];
    }

    /**
     * @return GroupAttrInterface[]
     */
    public function getAllGroups()
    {
        /**
         * @var GroupAttrInterface[] $items
         */
        $items = $this->groupAttributeCollection->getItems();
        return $items;
    }
}
