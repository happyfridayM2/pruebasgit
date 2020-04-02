<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Plugin\DataPost\Block\Product;

use Amasty\Cart\Plugin\DataPost\Replacer;
use Magento\Catalog\Block\Product\View\AddTo\Compare as ProductCompare;

class Compare extends Replacer
{
    /**
     * @param ProductCompare $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(ProductCompare $subject, $result)
    {
        if ($this->helper->isCompareAjax()) {
            $this->dataPostReplace($result);
        }

        return $result;
    }
}
