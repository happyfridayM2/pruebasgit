<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Plugin\Catalog\Helper;

use Magento\Catalog\Helper\Category as CategoryHelper;
use Amasty\ShopbyPage\Model\Page;

/**
 * Class Category
 *
 * @package Amasty\ShopbyPage\Plugin\Catalog\Helper
 */
class Category
{
    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    private $layerResolver;

    public function __construct(
        \Magento\Catalog\Model\Layer\Resolver $layerResolver
    ) {
        $this->layerResolver = $layerResolver;
    }

    /**
     * @return \Magento\Catalog\Model\Category|null
     */
    private function getCurrentCategory()
    {
        $catalogLayer = $this->layerResolver->get();

        if (!$catalogLayer) {
            return null;
        }

        return $catalogLayer->getCurrentCategory();
    }

    /**
     * @param CategoryHelper $category
     * @param $canUse
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function afterCanUseCanonicalTag(CategoryHelper $category, $canUse)
    {
        $currentCategory = $this->getCurrentCategory();

        if (!$canUse && $currentCategory !== null) {
            if ($currentCategory->getData(Page::CATEGORY_FORCE_USE_CANONICAL)) {
                $canUse = true;
            }
        }

        return $canUse;
    }
}
