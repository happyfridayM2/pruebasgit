<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Api;

use Magento\Catalog\Model\Category;

interface CategoryDataSetterInterface
{
    const APPLIED_BRAND_VALUE = 'applied_brand_customizer_value';

    public function setCategoryData(Category $category);
}
