<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Plugin\DataPost\Block\Widget;

use Amasty\Cart\Plugin\DataPost\Replacer;
use Magento\CatalogWidget\Block\Product\ProductsList as WidgetList;

class ProductsList extends Replacer
{
    /**
     * @param WidgetList $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(WidgetList $subject, $result)
    {
        $classes = [];
        if ($this->helper->isWishlistAjax()) {
            $classes[] = self::WISHLIST_REGEX;
        }
        if ($this->helper->isCompareAjax()) {
            $classes[] = self::COMPARE_REGEX;
        }

        $this->dataPostReplace($result, $classes);

        return $result;
    }
}
