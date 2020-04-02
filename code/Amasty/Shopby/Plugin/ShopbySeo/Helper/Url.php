<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\ShopbySeo\Helper;

use Amasty\ShopbyBrand\Helper\Data as BrandHelper;
use Amasty\Shopby\Helper\Data as ShopbyHelper;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Amasty\Shopby\Helper\Category;

/**
 * Class Url
 * @package Amasty\Shopby\Plugin\ShopbySeo\Helper
 */
class Url
{
    const CATEGORY_FILTER_PARAM_NAME = 'cat';
    const SHOPBY_EXTRA_PARAM = 'amshopby';

    /**
     * @var BrandHelper
     */
    private $brandHelper;

    /**
     * @var ShopbyHelper
     */
    private $shopbyHelper;

    /**
     * @var UrlRewrite[]
     */
    private $rewrites;

    /**
     * @var Category
     */
    private $shopbyCategoryHelper;

    public function __construct(
        BrandHelper $brandHelper,
        ShopbyHelper $shopbyHelper,
        CategoryCollectionFactory $categoryCollectionFactory,
        UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager,
        Category $categoryHelper
    ) {
        $this->brandHelper = $brandHelper;
        $this->shopbyHelper = $shopbyHelper;
        $this->shopbyCategoryHelper = $categoryHelper;
        $categoryIds = $categoryCollectionFactory->create()->getAllIds();
        $rewriteData = $urlFinder->findAllByData([
            UrlRewrite::ENTITY_ID => $categoryIds,
            UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::STORE_ID => $storeManager->getStore()->getId(),
            UrlRewrite::REDIRECT_TYPE => 0
        ]);
        foreach ($rewriteData as $rewrite) {
            $this->rewrites[$rewrite->getEntityId()] = $rewrite->getRequestPath();
        }
    }

    /**
     * @param $subject
     * @param $identifier
     * @param $preparedSeoAliases
     * @return array
     */
    public function beforeModifySeoIdentifierByAlias($subject, $identifier, $preparedSeoAliases)
    {
        $allProductsIdentifier = $this->shopbyHelper->getAllProductsUrlKey();
        if ($allProductsIdentifier == $identifier && $subject->getParam(self::CATEGORY_FILTER_PARAM_NAME)) {
            $categoryId = $subject->getParam(self::CATEGORY_FILTER_PARAM_NAME);
            if (is_array($categoryId)) {
                $categoryId = current($categoryId);
            }
            if (isset($this->rewrites[$categoryId])) {
                $identifier = $subject->removeCategorySuffix($this->rewrites[$categoryId]);
            }
        } elseif ($allProductsIdentifier == $identifier && !empty($preparedSeoAliases)) {
            $identifier = '';
        }
        return [$identifier, $preparedSeoAliases];
    }

    /**
     * @param $subject
     * @param array $result
     * @return array
     */
    public function afterParseQuery($subject, $result)
    {
        if ($subject->getParam(self::SHOPBY_EXTRA_PARAM)) {
            foreach ($subject->getParam(self::SHOPBY_EXTRA_PARAM) as $name => $value) {
                $subject->setParam($name, implode(',', $value));
            }
            $subject->setParam(self::SHOPBY_EXTRA_PARAM, null);
        }
        return $result;
    }

    /**
     * @param $subject
     * @param bool $result
     * @return bool
     */
    public function afterHasCategoryFilterParam($subject, $result)
    {
        return $result && !$this->shopbyCategoryHelper->isMultiselect();
    }
}
