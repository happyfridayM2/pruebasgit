<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\GroupAttr;

class CollectionProvider implements \Amasty\ShopbyBase\Api\GroupAttributeCollectionProvider
{
    /**
     * @var \Amasty\Shopby\Model\ResourceModel\GroupAttr\CollectionFactory
     */
    private $collectionFactory;

    /**
     * CollectionProvider constructor.
     * @param \Amasty\Shopby\Model\ResourceModel\GroupAttr\CollectionFactory $collectionFactory
     */
    public function __construct(\Amasty\Shopby\Model\ResourceModel\GroupAttr\CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Amasty\Shopby\Model\ResourceModel\GroupAttr\Collection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }
}
