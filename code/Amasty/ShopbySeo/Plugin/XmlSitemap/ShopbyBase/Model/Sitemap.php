<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Plugin\XmlSitemap\ShopbyBase\Model;

/**
 * Class Sitemap
 * @package Amasty\ShopbySeo\Plugin\XmlSitemap\ShopbyBase\Model
 */
class Sitemap
{
    /**
     * @var \Amasty\ShopbySeo\Helper\Url
     */
    private $helperUrl;

    public function __construct(
        \Amasty\ShopbySeo\Helper\Url $helperUrl
    ) {
        $this->helperUrl = $helperUrl;
    }

    /**
     * @param $subject
     * @param $url
     * @return string
     */
    public function afterApplySeoUrl($subject, $url)
    {
        if ($this->helperUrl->isSeoUrlEnabled()) {
            $url = $this->helperUrl->seofyUrl($url);
        }

        return $url;
    }
}
