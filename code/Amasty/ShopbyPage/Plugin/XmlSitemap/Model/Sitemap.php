<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Plugin\XmlSitemap\Model;

use Amasty\XmlSitemap\Model\Sitemap as NativeSitemap;

/**
 * Class Sitemap
 *
 * @package Amasty\ShopbyPage\Plugin\XmlSitemap\Model
 */
class Sitemap
{
    /**
     * @var \Amasty\ShopbyPage\Model\ResourceModel\Page\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Amasty\ShopbyPage\Model\ResourceModel\Page\CollectionFactory $collectionFactory
    ) {

        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param NativeSitemap $subject
     * @param \Closure $proceed
     * @param $storeId
     *
     * @return \Amasty\ShopbyPage\Model\ResourceModel\Page\Collection
     */
    public function aroundGetShopByPageCollection(NativeSitemap $subject, \Closure $proceed, $storeId)
    {
        /** @var \Amasty\ShopbyPage\Model\ResourceModel\Page\Collection $collection */
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('url', ['neq' => ''])
            ->addStoreFilter($storeId);

        return $collection;
    }
}
