<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Plugin\DataPost\Block\Compare;

use Amasty\Cart\Plugin\DataPost\Replacer;
use Magento\Catalog\Block\Product\Compare\ListCompare as CompareList;

class ListCompare extends Replacer
{
    /**
     * @param CompareList $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(CompareList $subject, $result)
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
