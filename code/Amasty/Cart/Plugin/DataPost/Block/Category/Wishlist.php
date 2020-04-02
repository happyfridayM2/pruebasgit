<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Plugin\DataPost\Block\Category;

use Amasty\Cart\Plugin\DataPost\Replacer;
use Magento\Wishlist\Block\Catalog\Product\ProductList\Item\AddTo\Wishlist as CategoryWishlist;

class Wishlist extends Replacer
{
    /**
     * @param CategoryWishlist $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(CategoryWishlist $subject, $result)
    {
        if ($this->helper->isWishlistAjax()) {
            $this->dataPostReplace($result);
        }

        return $result;
    }
}
