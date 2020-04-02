<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Model\Source;

/**
 * Class Tooltip
 *
 * @package Amasty\ShopbyBrand\Model\Source
 */
class Tooltip implements \Magento\Framework\Option\ArrayInterface
{
    const NO_DISPLAY = 'no';
    const ALL_BRAND_PAGE = 'all_brands';
    const PRODUCT_PAGE = 'product_page';
    const LISTING_PAGE = 'listing_page';

    public function toOptionArray()
    {
        return [
            ['value' => self::NO_DISPLAY, 'label' => __('No')],
            ['value' => self::ALL_BRAND_PAGE, 'label' => __('All Brands page')],
            ['value' => self::PRODUCT_PAGE, 'label' => __('Product page')],
            ['value' => self::LISTING_PAGE, 'label' => __('Listing page')],
        ];
    }
}
