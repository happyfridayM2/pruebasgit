<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

namespace Amasty\Shopby\Model\Source;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\StoreManager;

/**
 * Class Category
 * @package Amasty\Shopby\Model\Source
 */
class Category
{
    const SYSTEM_CATEGORY_ID = 1;
    const ROOT_LEVEL = 1;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var bool
     */
    private $emptyOption = true;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * Category constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManager $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options = [];
        if ($this->emptyOption) {
            $options[0] = ' ';
        }

        $options = array_replace(
            $options,
            $this->getChildren(self::SYSTEM_CATEGORY_ID, self::ROOT_LEVEL)
        );

        return $options;
    }

    private function getChildren($parentCategoryId, $level)
    {
        $options = [];
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToFilter('level', $level);
        $collection->addAttributeToFilter('parent_id', $parentCategoryId);
        $collection->setOrder('position', 'asc');
        foreach ($collection as $category) {
            $options[$category->getId()] =
                str_repeat(". ", max(0, ($category->getLevel() - 1) * 3)) . $category->getName();
            if ($category->hasChildren()) {
                $options = array_replace($options, $this->getChildren($category->getId(), $category->getLevel() + 1));
            }
        }
        return $options;
    }

    /**
     * @param bool $emptyOption
     * @return $this
     */
    public function setEmptyOption($emptyOption)
    {
        $this->emptyOption = $emptyOption;

        return $this;
    }
}
