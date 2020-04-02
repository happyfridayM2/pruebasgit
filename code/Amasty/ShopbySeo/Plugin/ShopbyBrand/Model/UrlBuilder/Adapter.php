<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Plugin\ShopbyBrand\Model\UrlBuilder;

/**
 * Class Adapter
 * @package Amasty\ShopbySeo\Plugin\ShopbyBrand\Model\UrlBuilder
 */
class Adapter
{
    /**
     * @var \Amasty\ShopbySeo\Helper\Url
     */
    private $urlHelper;

    public function __construct(\Amasty\ShopbySeo\Helper\Url $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param $subject
     * @param $result
     * @return string|null
     */
    public function afterGetSuffix($subject, $result)
    {
        if ($this->urlHelper->isAddSuffixToShopby()) {
            return $this->urlHelper->getSeoSuffix();
        }
        return $result;
    }
}
