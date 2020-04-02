<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Block\Customer;

use Magento\Catalog\Model\Product;
use Amasty\Xnotif\Block\AbstractBlock;

/**
 * Class PriceRender
 */
class PriceRender extends AbstractBlock
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @param Product $product
     *
     * @return $this
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return Product
     */
    public function getProductItem()
    {
        return $this->product;
    }
}
