<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Observer;

class SalesQuoteProductAddAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Wyomind\OrdersExportTool\Model\ResourceModel\Product\Collection|null
     */
    protected $_productCollection = null;

    /**
     * SalesQuoteProductAddAfter constructor.
     * @param \Wyomind\OrdersExportTool\Model\ResourceModel\Product\Collection $productCollection
     */
    public function __construct(
        \Wyomind\OrdersExportTool\Model\ResourceModel\Product\Collection $productCollection
    )
    {
        $this->_productCollection = $productCollection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $ids = [];
        foreach ($observer->getItems() as $item) {
            $ids[] = $item->getProductId();
        }

        $this->_productCollection->searchProducts($ids);
        foreach ($this->_productCollection as $product) {
            $profileId = $product->getExportTo();
            if ($profileId) {
                $item->setData('export_to', $profileId);
            } else {
                $item->setData('export_to', 0);
            }
        }
    }
}