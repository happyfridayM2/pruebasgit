<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Category;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Category Repository
 *
 * @package Amasty\Feed
 */
class Repository
{
    /**
     * @var \Amasty\Feed\Model\Category\CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var \Amasty\Feed\Model\Category\ResourceModel\Category
     */
    private $categoryResource;

    /**
     * @var \Amasty\Feed\Model\Category\Category[]
     */
    private $categories;

    /**
     * @var ResourceModel\MappingCollectionFactory
     */
    private $mappingCollectionFactory;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $categoryCollectionFactory;

    public function __construct(
        \Amasty\Feed\Model\Category\CategoryFactory $categoryFactory,
        \Amasty\Feed\Model\Category\ResourceModel\CollectionFactory $categoryCollectionFactory,
        \Amasty\Feed\Model\Category\ResourceModel\Category $categoryResource,
        \Amasty\Feed\Model\Category\ResourceModel\MappingCollectionFactory $mappingCollectionFactory
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->mappingCollectionFactory = $mappingCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function save(\Amasty\Feed\Model\Category\Category $category)
    {
        try {
            if ($category->getFeedCategoryId()) {
                $category = $this->getById($category->getFeedCategoryId())->addData($category->getData());
            }

            $this->categoryResource->save($category);

            if ($mapping = $category->getMapping()) {
                $collection = $this->mappingCollectionFactory->create();
                $collection->addFieldToFilter('feed_category_id', $category->getId())->walk('delete');
                unset($mapping[0]); //Remove record with category id 0

                foreach ($mapping as $mappingCategoryId => $mappingData) {
                    /** @var \Amasty\Feed\Model\Category\Mapping $mappingModel */
                    $mappingModel = $collection->getNewEmptyItem();
                    $mappingModel->setFeedCategoryId($category->getId())
                        ->setCategoryId($mappingCategoryId)
                        ->setVariable(isset($mappingData['name']) ? $mappingData['name'] : null)
                        ->setSkip(isset($mappingData['skip']) ? $mappingData['skip'] : false);
                    $collection->addItem($mappingModel);
                }

                $collection->save();
            }

            unset($this->categories[$category->getFeedCategoryId()]);
        } catch (\Exception $e) {
            if ($category->getFeedCategoryId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save category with ID %1. Error: %2',
                        [$category->getFeedCategoryId(), $e->getMessage()]
                    )
                );
            }

            throw new CouldNotSaveException(__('Unable to save new category. Error: %1', $e->getMessage()));
        }

        return $category;
    }

    public function getById($categoryId)
    {
        if (!isset($this->categories[$categoryId])) {
            /** @var \Amasty\Feed\Model\Category\Category $category */
            $category = $this->categoryFactory->create();
            $this->categoryResource->load($category, $categoryId);

            if (!$category->getFeedCategoryId()) {
                throw new NoSuchEntityException(__('Category with specified ID "%1" not found.', $categoryId));
            }

            $this->getCategoryDeps($category);
            $this->categories[$categoryId] = $category;
        }

        return $this->categories[$categoryId];
    }

    public function deleteById($categoryId)
    {
        return $this->delete($this->getById($categoryId));
    }

    public function delete(\Amasty\Feed\Model\Category $category)
    {
        try {
            $this->categoryResource->delete($category);
            unset($this->icons[$icon->getIconId()]);
        } catch (\Exception $e) {
            if ($category->getFeedCategoryId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove category with ID %1. Error: %2',
                        [$icon->getIconId(), $e->getMessage()]
                    )
                );
            }

            throw new CouldNotDeleteException(__('Unable to remove category. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @return Category
     */
    public function getCategoryEmptyEntity()
    {
        return $this->categoryFactory->create();
    }

    /**
     * @return ResourceModel\Collection
     */
    public function getCategoryEmptyCollection()
    {
        return $this->categoryCollectionFactory->create();
    }

    public function getItemsWithDeps(\Amasty\Feed\Model\Category\ResourceModel\Collection $collection)
    {
        $result = [];

        foreach ($collection->getItems() as $category) {
            $result[] = $this->getCategoryDeps($category);
        }

        return $result;
    }

    /**
     * @param Category $category
     *
     * @return Category
     */
    private function getCategoryDeps(Category $category)
    {
        /** @var \Amasty\Feed\Model\Category\ResourceModel\MappingCollection $collection */
        $collection = $this->mappingCollectionFactory->create();
        $collection->addFieldToFilter('feed_category_id', $category->getId());
        $mapping = [];

        foreach ($collection as $mappedCategory) {
            $mappedCategory->setName($mappedCategory->getVariable())
                ->setSkip($mappedCategory->getSkip());
            $mapping[$mappedCategory->getCategoryId()] = $mappedCategory;
        }

        $category->setMapping($mapping);

        return $category;
    }
}
