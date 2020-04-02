<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product;

/**
 * Class Collection
 * @package Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product
 */
class Collection
{
    /**
     * @var \Amasty\Shopby\Model\Layer\Cms\Manager
     */
    protected $cmsManager;

    /**
     * @param \Amasty\Shopby\Model\Layer\Cms\Manager $cmsManager
     */
    public function __construct(
        \Amasty\Shopby\Model\Layer\Cms\Manager $cmsManager
    ) {
        $this->cmsManager = $cmsManager;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return array
     */
    public function beforeGetItems(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection)
    {
        if ($this->cmsManager->isCmsPageNavigation()) {
            $this->cmsManager->applyIndexStorage($collection);
        }
        return [];
    }
}
