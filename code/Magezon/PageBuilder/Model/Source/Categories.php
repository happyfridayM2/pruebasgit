<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_PageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\PageBuilder\Model\Source;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Catalog\Model\Category as CategoryModel;

class Categories
{
    /**#@+
     * Category tree cache id
     */
    const CATEGORY_TREE_ID = 'MAGEZON_BUILDERS_CATEGORY_TREE';

    /**
     * @var CacheInterface
     */
    private $cacheManager;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var DbHelper
     */
    private $dbHelper;

    /**
     * @var \Magezon\Core\Helper\Data
     */
    private $coreHelper;

    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory 
     * @param DbHelper                  $dbHelper                  
     * @param \Magezon\Core\Helper\Data $coreHelper                
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        DbHelper $dbHelper,
        \Magezon\Core\Helper\Data $coreHelper
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->dbHelper                  = $dbHelper;
        $this->coreHelper                = $coreHelper;
    }

	public function getConfig()
	{
		return $this->getOptions();
	}

	public function getOptions()
	{
		$categoryTree = $this->getCacheManager()->load(self::CATEGORY_TREE_ID);
        if ($categoryTree) {
            return $this->coreHelper->unserialize($categoryTree);
        }

		$collection = $this->categoryCollectionFactory->create();
		$collection->addAttributeToSelect('name');
		$options    = [];
		foreach ($collection as $category) {
			$categoryLabel = $this->getSpaces($category['level']) . '(ID:' . $category->getId() . ') ' . $category->getName();
            $category['label'] = $categoryLabel;
			$options[] = [
				'value'       => $category->getId(),
				'label'       => $categoryLabel,
				'short_label' => '(ID:' . $category->getId() . ')' . $category->getName()
			];
		}

		$this->getCacheManager()->save(
            $this->coreHelper->serialize($options),
            self::CATEGORY_TREE_ID,
            [
                \Magento\Framework\App\Cache\Type\Block::CACHE_TAG
            ]
        );

		return $options;
	}

    protected function getSpaces($number)
    {
        $s = '';
        for($i = 0; $i < $number; $i++) {
            $s .= '_ ';
        }
        return $s;
    }

    /**
     * Retrieve cache interface
     *
     * @return CacheInterface
     * @deprecated 101.0.3
     */
    private function getCacheManager()
    {
        if (!$this->cacheManager) {
            $this->cacheManager = ObjectManager::getInstance()
                ->get(CacheInterface::class);
        }
        return $this->cacheManager;
    }

    /**
     * Retrieve categories tree
     *
     * @param string|null $filter
     * @return array
     * @since 101.0.0
     */
    public function getCategoriesTree($storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID)
    {
        $categoryTree = $this->getCacheManager()->load(self::CATEGORY_TREE_ID . '_' . $storeId);
        if ($categoryTree) {
            //return $this->coreHelper->unserialize($categoryTree);
        }

        /* @var $matchingNamesCollection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $matchingNamesCollection = $this->categoryCollectionFactory->create();

        $matchingNamesCollection->addAttributeToSelect('path')
            ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID])
            ->setStoreId($storeId);

        $shownCategoriesIds = [];

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($matchingNamesCollection as $category) {
            foreach (explode('/', $category->getPath()) as $parentId) {
                $shownCategoriesIds[$parentId] = 1;
            }
        }

        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection = $this->categoryCollectionFactory->create();

        $collection->addAttributeToFilter('entity_id', ['in' => array_keys($shownCategoriesIds)])
            ->addAttributeToSelect(['name', 'is_active', 'parent_id'])
            ->setStoreId($storeId);

        $categoryById = [
            CategoryModel::TREE_ROOT_ID => [
                'value' => CategoryModel::TREE_ROOT_ID,
                'optgroup' => null,
            ],
        ];

        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['value' => $categoryId];
                }
            }

            $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
            $categoryById[$category->getId()]['label'] = $category->getName();
            $categoryById[$category->getId()]['name'] = $category->getName();
            $categoryById[$category->getId()]['id'] = $category->getId();
            $categoryById[$category->getId()]['url'] = $category->getUrl();
            $categoryById[$category->getId()]['product_count'] = $category->getProductCount();
            $categoryById[$category->getParentId()]['optgroup'][] = &$categoryById[$category->getId()];
        }

        $this->getCacheManager()->save(
            $this->coreHelper->serialize($categoryById[CategoryModel::TREE_ROOT_ID]['optgroup']),
            self::CATEGORY_TREE_ID . '_' . $storeId,
            [
                \Magento\Catalog\Model\Category::CACHE_TAG,
                \Magento\Framework\App\Cache\Type\Block::CACHE_TAG
            ]
        );

        return $categoryById[CategoryModel::TREE_ROOT_ID]['optgroup'];
    }
}