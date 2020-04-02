<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Plugin\XmlSitemap\Model;

use Amasty\XmlSitemap\Model\Sitemap as AmastySiteMap;

/**
 * Class Sitemap
 */
class Sitemap
{
    /**
     * @var \Amasty\ShopbyBase\Model\XmlSitemap
     */
    private $xmlSitemap;

    public function __construct(
        \Amasty\ShopbyBase\Model\XmlSitemap $xmlSitemap
    ) {
        $this->xmlSitemap = $xmlSitemap;
    }

    /**
     * @param AmastySiteMap $subject
     * @param \Closure $proceed
     * @param $storeId
     *
     * @return array
     */
    public function aroundGetBrandCollection(AmastySiteMap $subject, \Closure $proceed, $storeId)
    {
        return $this->xmlSitemap->getBrandUrls($storeId);
    }
}
