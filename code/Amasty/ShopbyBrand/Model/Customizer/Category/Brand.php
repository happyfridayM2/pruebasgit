<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Model\Customizer\Category;

use Amasty\ShopbyBase\Api\CategoryDataSetterInterface;
use Amasty\ShopbyBase\Model\Customizer\Category\CustomizerInterface;
use Magento\Catalog\Model\Category;
use Amasty\ShopbyBase\Model\Category\Manager as CategoryManager;

/**
 * Class Brand
 *
 * @package Amasty\ShopbyBrand\Model\Customizer\Category
 */
class Brand implements CustomizerInterface
{
    const APPLY_TO_HEADING = 'am_apply_to_heading';
    const APPLY_TO_META = 'am_apply_to_meta';
    /**
     * @var \Amasty\ShopbyBrand\Helper\Content
     */
    private $brandContentHelper;

    /**
     * @var  Category
     */
    private $category;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $layer;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Amasty\ShopbyBase\Helper\OptionSetting
     */
    private $optionSettingHelper;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Amasty\ShopbyBrand\Helper\Content $brandContentHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\App\RequestInterface $request,
        \Amasty\ShopbyBase\Helper\OptionSetting $optionSettingHelper,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Page\Config $pageConfig
    ) {
        $this->brandContentHelper = $brandContentHelper;
        $this->layer = $layerResolver->get();
        $this->request = $request;
        $this->optionSettingHelper = $optionSettingHelper;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->pageConfig = $pageConfig;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function prepareData(Category $category)
    {
        $brand = $this->brandContentHelper->getCurrentBranding();
        if (!$brand) {
            return $this;
        }

        $this->category = $category;

        $data = $this->getOptionData();

        $this->setTitle($data['title'])
            ->setDescription($data['description'])
            ->setImg($data['img_url'])
            ->setCmsBlock($data['cms_block'])
            ->setMetaTitle($data['meta_title'])
            ->setMetaDescription($data['meta_description'])
            ->setMetaKeywords($data['meta_keywords'])
            ->setBottomCmsBlock($data['bottom_cms_block']);
        $category->setData(CategoryDataSetterInterface::APPLIED_BRAND_VALUE, $brand->getValue());

        return $this;
    }

    /**
     * @return array
     */
    private function getOptionData()
    {
        $result = [
            'title' => [],
            'description' => [],
            'cms_block' => null,
            'img_url' => null,
            'meta_title' => [],
            'meta_description' => [],
            'meta_keywords' => [],
            'bottom_cms_block' => null
        ];

        $setting = $this->brandContentHelper->getCurrentBranding();

        if ($setting->getTitle()) {
            $result['title'][] = $setting->getTitle();
        }
        if ($setting->getDescription()) {
            $result['description'][] = $setting->getDescription(true);
        }
        if ($setting->getTopCmsBlockId() && $result['cms_block'] === null) {
            $result['cms_block'] = $setting->getTopCmsBlockId();
        }
        if ($setting->getBottomCmsBlockId() && $result['bottom_cms_block'] === null) {
            $result['bottom_cms_block'] = $setting->getBottomCmsBlockId();
        }
        if ($setting->getImageUrl() && $result['img_url'] === null) {
            $result['img_url'] = $setting->getImageUrl();
        }

        if ($setting->getMetaTitle()) {
            $result['meta_title'][] = $setting->getMetaTitle();
        }
        if ($setting->getMetaDescription()) {
            $result['meta_description'][] = $setting->getMetaDescription();
        }
        if ($setting->getMetaKeywords()) {
            $result['meta_keywords'][] = $setting->getMetaKeywords();
        }

        return $result;
    }

    /**
     * Set category title.
     * @param array $title
     * @return $this
     */
    private function setTitle($title)
    {
        if ($title) {
            $this->category->setName(join('', $title));
        }

        return $this;
    }

    /**
     * Set category meta title.
     * @param array $metaTitle
     * @return $this
     */
    private function setMetaTitle($metaTitle)
    {
        if ($metaTitle) {
            $this->category->setData('meta_title', join('', $metaTitle));
        }

        return $this;
    }

    /**
     * Set category description.
     * @param array $description
     * @return $this
     */
    private function setDescription($description)
    {
        if ($description) {
            $description = '<span class="amshopby-descr">' . join('<br>', $description) . '</span>';
            $this->category->setData('description', $description);
        }
        return $this;
    }

    /**
     * Set category meta description.
     * @param array $metaDescription
     * @return $this
     */
    private function setMetaDescription(array $metaDescription)
    {
        if ($metaDescription) {
            $this->category->setData('meta_description', join('', $metaDescription));
        }

        return $this;
    }

    /**
     * Set category meta keywords.
     * @param array $metaKeywords
     * @return $this
     */
    private function setMetaKeywords($metaKeywords)
    {
        if ($metaKeywords) {
            $this->category->setData('meta_keywords', join('', $metaKeywords));
        }

        return $this;
    }

    /**
     * Set category image.
     * @param string|null $imgUrl
     * @return $this
     */
    private function setImg($imgUrl)
    {
        if ($imgUrl !== null) {
            $this->category->setData(CategoryManager::CATEGORY_SHOPBY_IMAGE_URL, $imgUrl);
        }
        return $this;
    }

    /**
     * Set category CMS block.
     * @param string|null $blockId
     * @return $this
     */
    private function setCmsBlock($blockId)
    {
        if ($blockId !== null) {
            $this->category->setData('landing_page', $blockId);
            $this->category->setData(CategoryManager::CATEGORY_FORCE_MIXED_MODE, 1);
        }
        return $this;
    }

    /**
     * Set category bottom CMS block.
     * @param string|null $blockId
     * @return $this
     */
    private function setBottomCmsBlock($blockId)
    {
        if ($blockId !== null) {
            $this->category->setData('bottom_cms_block', $blockId);
            $this->category->setData(CategoryManager::CATEGORY_FORCE_MIXED_MODE, 1);
        }

        return $this;
    }
}
