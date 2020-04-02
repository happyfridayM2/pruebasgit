<?php

namespace Radosav\Reportfix\Model\Magento\Reports\ResourceModel\Quote\Item;


class Collection extends \Magento\Reports\Model\ResourceModel\Quote\Item\Collection
{
    protected function _afterLoad()
    {
        $items = $this->getItems();
        $productIds = [];
        foreach ($items as $item) {
            $productIds[] = $item->getProductId();
        }
        $productData = $this->getProductData($productIds);
        $orderData = $this->getOrdersData($productIds);
        $existing = $this->productResource->getAllIds();
        foreach ($items as $item) {
            if(in_array($item->getProductId(),$existing)){
                continue;
            }
            @$item->setId($item->getProductId());
            @$item->setPrice($productData[$item->getProductId()]['price'] * $item->getBaseToGlobalRate());
            @$item->setName($productData[$item->getProductId()]['name']);
            @$item->setOrders(0);
            if (isset($orderData[$item->getProductId()])) {
                $item->setOrders($orderData[$item->getProductId()]['orders']);
            }
        }

        return $this;
    }
}
	
	
