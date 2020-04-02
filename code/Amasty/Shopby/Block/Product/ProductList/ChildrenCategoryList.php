<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Product\ProductList;

use Magento\Framework\View\Element\Template;
use Amasty\Shopby\Model\Source\ChildrenCategoriesBlock\Categories;
use Amasty\Shopby\Model\Source\ChildrenCategoriesBlock\DisplayMode;

/**
 * Class ChildrenCategoryList
 * @package Amasty\Shopby\Block\Product\ProductList
 * @api
 */
class ChildrenCategoryList extends Template
{
    const DEFAULT_SLIDES_COUNT = 5;
    const DEFAULT_IMAGE_SIZE = 100;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\Shopby\Helper\Category
     */
    private $categoryHelper;

    /**
     * @var \Magento\Catalog\Model\Category[]
     */
    private $childrenCategories = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    private $categoryCollection;

    /**
     * @var array
     */
    private $availableModules = ['catalog', 'catalogsearch'];

    private $request;

    /**
     * ChildrenCategoryList constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Amasty\Shopby\Helper\Category $categoryHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Shopby\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $coreRegistry;
        $this->categoryHelper = $categoryHelper;
        $this->categoryCollection = $categoryCollection;
        $this->request = $context->getRequest();
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param int $size
     *
     * @return bool|string|null
     */
    public function getCategoryImageUrl(\Magento\Catalog\Model\Category $category, $size)
    {
        return $this->categoryHelper->getImageUrl(
            $category->getThumbnail() ?: $category->getImage(),
            true,
            $size,
            $size
        );
    }

    /**
     * @return array
     */
    public function getChildrenCategories()
    {
        if (empty($this->childrenCategories)) {
            /**
             * @var \Magento\Catalog\Model\Category $currentCategory
             */
            $currentCategory = $this->registry->registry('current_category');
            $collection = $currentCategory->getChildrenCategories();

            if ($collection instanceof \Magento\Catalog\Model\ResourceModel\Category\Collection) {

                /**
                 * @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection
                 */
                $collection->setLoadProductCount(true);
                $collection->addNameToResult();
                $collection->addOrderField('name');
                $collection->addAttributeToSelect('image');
                $collection->addAttributeToSelect('thumbnail');
                $collection->addAttributeToFilter('is_active', ['eq' => true]);
                if ($this->getData('attributes_to_select')) {
                    $collection->addAttributeToSelect($this->getData('attributes_to_select'));
                }
            } elseif (is_array($collection) && !empty($collection)) {
                $this->categoryCollection->loadProductCount($collection);
            }

            foreach ($collection as $category) {
                if ($category->getData('product_count') || $category->getProductCount()) {
                    $this->childrenCategories[] = $category;
                }
            }
        }

        return $this->childrenCategories;
    }

    /**
     * @return int
     */
    public function getItemsCountPerSlide()
    {
        return $this->categoryHelper->getChildrenCategoriesItemsCountPerSlide() ?: self::DEFAULT_SLIDES_COUNT;
    }

    /**
     * @return int
     */
    public function getImageSize()
    {
        return $this->categoryHelper->getChildrenCategoriesBlockImageSize();
    }

    /**
     * @return bool
     */
    public function showLabels()
    {
        return $this->categoryHelper->showChildrenCategoriesImageLabels();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->categoryHelper->getChildrenCategoriesBlockDisplayMode()
            && in_array($this->request->getModuleName(), $this->availableModules)
            && $this->isAllowInCategory()
        ) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return bool
     */
    private function isAllowInCategory()
    {
        $currentCategoryId = $this->registry->registry('current_category')->getId();
        $allowCategories = $this->categoryHelper->getAllowCategories();

        return in_array($currentCategoryId, explode(',', $allowCategories))
            || $allowCategories == Categories::ALL_CATEGORIES;
    }

    /**
     * @return bool
     */
    public function isOnlyLabels()
    {
        return $this->categoryHelper->getChildrenCategoriesBlockDisplayMode() == DisplayMode::LABELS;
    }

    /**
     * @return bool
     */
    public function isSliderEnabled()
    {
        return $this->categoryHelper->isChildrenCategoriesSliderEnabled()
            && count($this->getChildrenCategories()) > $this->getItemsCountPerSlide();
    }
}
