<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\GroupAttr;

use Amasty\Conf\Api\Data\GroupAttrInterface;

class DataProvider
{
    const ENABLED = 1;

    /**
     * @var \Amasty\Conf\Model\ResourceModel\GroupAttr\Collection
     */
    private $groupAttributeCollection;

    /**
     * @var \Amasty\Conf\Model\ResourceModel\GroupAttrOption\Collection
     */
    private $groupAttributeOptionCollection;

    /**
     * @var GroupAttrInterface[][]
     */
    private $groupByAttributeId = [];

    public function __construct(
        \Amasty\Conf\Model\ResourceModel\GroupAttr\CollectionFactory $groupAttributeCollectionFactory,
        \Amasty\Conf\Model\ResourceModel\GroupAttrOption\CollectionFactory $groupAttributeOptionCollectionFactory
    ) {
        $this->groupAttributeCollection = $groupAttributeCollectionFactory->create();
        $this->groupAttributeOptionCollection = $groupAttributeOptionCollectionFactory->create();
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
