<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Plugin\Xsearch\Block\Search;

use Amasty\ShopbyBrand\Block\Widget\BrandListFactory;

/**
 * Class Brand
 *
 * @package Amasty\ShopbyBrand\Plugin\Xsearch\Block\Search
 */
class Brand
{
    /**
     * @var BrandListFactory
     */
    private $brandListFactory;

    public function __construct(BrandListFactory $brandListFactory)
    {
        $this->brandListFactory = $brandListFactory;
    }

    /**
     * @param \Amasty\Xsearch\Block\Search\Brand $subject
     * @param array $result
     * @return array
     */
    public function afterGetBrands($subject, array $result)
    {
        return array_merge($result, $this->brandListFactory->create()->getItems());
    }
}
