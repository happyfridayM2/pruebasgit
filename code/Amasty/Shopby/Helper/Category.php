<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Helper;

use Amasty\Shopby\Model\Source\RenderCategoriesLevel;
use Magento\Store\Model\ScopeInterface;
use Amasty\Shopby\Model\Category\Attribute\Frontend\Image as ImageModel;

/**
 * Class Category
 * @package Amasty\Shopby\Helper
 */
class Category extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ATTRIBUTE_CODE = 'category_ids';
    const STORE_CODE = 'store_id';
    const CHILDREN_CATEGORIES_SETTING_PATH = 'amshopby/children_categories/';
    const DEFAULT_CATEGORY_FILTER_IMAGE_SIZE = 20;
    const MIN_CATEGORY_DEPTH = 1;
    const CATEGORY_FILTER_PARAM = 'cat';

    /**
     * @var \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    protected $setting;

    /**
     * @var \Amasty\ShopbyBase\Model\Category\Manager\Proxy
     */
    protected $categoryManager;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $layer;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    private $layerResolver;
    
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $startCategory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var array
     */
    protected $categoryImageById;

    /**
     * @var ImageModel
     */
    protected $image;

    /** @var FilterSetting  */
    private $settingHelper;

    /**
     * Category constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param FilterSetting $settingHelper
     * @param \Amasty\ShopbyBase\Model\Category\Manager\Proxy $categoryManager
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Amasty\Shopby\Helper\FilterSetting $settingHelper,
        \Amasty\ShopbyBase\Model\Category\Manager $categoryManager,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        ImageModel $image
    ) {
        parent::__construct($context);
        $this->settingHelper = $settingHelper;
        $this->categoryManager = $categoryManager;
        $this->layerResolver = $layerResolver;
        $this->categoryRepository = $categoryRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->image = $image;
    }

    /**
     * @return mixed
     */
    public function getStartCategory()
    {
        if ($this->startCategory === null) {
            $this->init();
        }

        return $this->startCategory;
    }

    /**
     * @return bool
     */
    public function isCategoryFilterExtended()
    {
        return $this->getSetting()->getCategoryTreeDepth() > 1;
    }

    /**
     * Category filter initialization
     *
     * @return $this
     */
    protected function init()
    {
        if ($this->getSetting()->getCategoryTreeDepth() ==  self::MIN_CATEGORY_DEPTH
            && !$this->getSetting()->getRenderAllCategoriesTree()
            && $this->getLayer()->getCurrentCategory()->getChildrenCount()
        ) {
            $category = $this->getLayer()->getCurrentCategory();
        } elseif ($this->getSetting()->getRenderCategoriesLevel() == RenderCategoriesLevel::ROOT_CATEGORY
            || !!$this->getSetting()->getRenderAllCategoriesTree()
            || $this->getSetting()->getCategoryTreeDepth() ==  self::MIN_CATEGORY_DEPTH
        ) {
            $category = $this->categoryRepository->get(
                $this->categoryManager->getRootCategoryId(),
                $this->categoryManager->getCurrentStoreId()
            );
        } elseif ($this->getSetting()->getRenderCategoriesLevel() == RenderCategoriesLevel::CURRENT_CATEGORY_LEVEL) {
            if ($this->getLayer()->getCurrentCategory()->getId() == $this->categoryManager->getRootCategoryId()) {
                $category = $this->getLayer()->getCurrentCategory();
            } else {
                $categoryId = $this->getLayer()->getCurrentCategory()->getParentId();
                $category = $this->categoryRepository->get($categoryId, $this->categoryManager->getCurrentStoreId());
            }
        } else { //  RenderCategoriesLevel::CURRENT_CATEGORY_CHILDREN
            $category = $this->getLayer()->getCurrentCategory();
        }
        $this->startCategory = $category;

        return $this;
    }

    /**
     * @param $categoryId
     * @param string $imageType
     * @return string|null
     */
    protected function getCategoryImage($categoryId, $imageType = 'thumbnail')
    {
        if (empty($this->categoryImageById[$imageType])) {
            $collection = $this->categoryCollectionFactory->create();
            $collection->addAttributeToSelect($imageType);
            foreach ($collection as $item) {
                $this->categoryImageById[$imageType][$item->getId()] = $item->getData($imageType);
            }
        }
        return isset($this->categoryImageById[$imageType][$categoryId])
            ? $this->categoryImageById[$imageType][$categoryId] : null;
    }

    /**
     * @param int $categoryId
     * @param string $imageType
     * @return string
     */
    public function getCategoryImageUrl($categoryId, $imageType = 'thumbnail')
    {
        return $this->getImageUrl(
            $this->getCategoryImage($categoryId, $imageType),
            true,
            $this->getCategoryFilterImageSize()
        );
    }

    /**
     * @param $imageName
     * @param bool $withPlaceholder
     * @param null $width
     * @param null $height
     * @return bool|null|string
     */
    public function getImageUrl($imageName, $withPlaceholder = false, $width = null, $height = null)
    {
        return $this->image->getImageUrl($imageName, $withPlaceholder, $width, $height);
    }

    /**
     * @return int
     */
    public function getCategoryFilterImageSize()
    {
        return self::DEFAULT_CATEGORY_FILTER_IMAGE_SIZE;
    }

    /**
     * @param string $path
     * @param bool $flag
     * @return bool|mixed
     */
    private function getChildrenCategoriesSetting($path, $flag = false)
    {
        if ($flag) {
            return $this->scopeConfig->isSetFlag(
                self::CHILDREN_CATEGORIES_SETTING_PATH . $path,
                ScopeInterface::SCOPE_STORE
            );
        }
        return $this->scopeConfig->getValue(
            self::CHILDREN_CATEGORIES_SETTING_PATH . $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getChildrenCategoriesBlockDisplayMode()
    {
        return $this->getChildrenCategoriesSetting('display_mode');
    }

    /**
     * @return string
     */
    public function getAllowCategories()
    {
        return $this->getChildrenCategoriesSetting('categories');
    }

    /**
     * @return bool
     */
    public function isChildrenCategoriesSliderEnabled()
    {
        return $this->getChildrenCategoriesSetting('slider_enabled', true);
    }

    /**
     * @return int
     */
    public function getChildrenCategoriesBlockImageSize()
    {
        return $this->getChildrenCategoriesSetting('image_size');
    }

    /**
     * @return int
     */
    public function getChildrenCategoriesItemsCountPerSlide()
    {
        return $this->getChildrenCategoriesSetting('items_per_slide');
    }

    /**
     * @return bool
     */
    public function showChildrenCategoriesImageLabels()
    {
        return $this->getChildrenCategoriesSetting('show_labels', true);
    }

    /**
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getSetting()
    {
        if ($this->setting === null) {
            $this->setting = $this->settingHelper->getSettingByAttributeCode(self::ATTRIBUTE_CODE);
        }

        return $this->setting;
    }

    /**
     * @return \Magento\Catalog\Model\Layer
     */
    public function getLayer()
    {
        if (!$this->layer) {
            $this->layer = $this->layerResolver->get();
        }
        return $this->layer;
    }

    /**
     * @return bool
     */
    public function isMultiselect()
    {
        return $this->getSetting()->isMultiselect();
    }
}
