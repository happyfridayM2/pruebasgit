<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Plugin\Catalog\Model;

use Magento\Catalog\Model\Category as MagentoCategory;
use Amasty\ShopbyBase\Model\Category\Manager as CategoryManager;

/**
 * Class Category
 */
class Category
{
    /**
     * @param MagentoCategory $subject
     * @param string|null $result
     * @return string|null
     */
    public function afterGetImageUrl(MagentoCategory $subject, $result)
    {
        if ($subject->hasData(CategoryManager::CATEGORY_SHOPBY_IMAGE_URL)) {
            return $subject->getData(CategoryManager::CATEGORY_SHOPBY_IMAGE_URL);
        }

        return $result;
    }
}
