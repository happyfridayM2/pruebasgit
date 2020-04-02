<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Observer;

class CheckoutSubmitAllAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository $productRepository
     */
    protected $_productRepository;
    
    /**
     * @var \Wyomind\Core\Helper\Data 
     */
    protected $_helperData;
    
    /**
     * @var \Wyomind\OrdersExportTool\Model\ProfilesFactory 
     */
    protected $_modelProfiles;
    
    /**
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Wyomind\Core\Helper\Data $helperData
     * @param \Wyomind\OrdersExportTool\Model\ProfilesFactory $modelProfilesFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Wyomind\Core\Helper\Data $helperData,
        \Wyomind\OrdersExportTool\Model\ProfilesFactory $modelProfilesFactory
    )
    {
        $this->_productRepository = $productRepository;
        $this->_helperData = $helperData;
        $this->_modelProfiles = $modelProfilesFactory->create();
    }

    /**
     * Execute on or several profile on event order submit after all
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // Add the profile id to each items (export_to column)
        $order = $observer->getEvent()->getData('order');
        
        foreach ($order->getItems() as $item) {
            $id = $item->getProductId();
            
            try {
                $product = $this->_productRepository->get($item->getSku());
            } catch (\Exception $e) {
                $id = $item->getProductId();
                $product = $this->_productRepository->getById($id);
            }
            
            if(isset($product)) {
                $profileId = $product->getExportTo();
                if ($profileId) {
                    $item->setExportTo($profileId);
                    $item->save();
                }
            }
        }
        
        $collection = $this->_modelProfiles->getCollection()->searchProfiles(explode(',', $this->_helperData->getStoreConfig("ordersexporttool/advanced/execute_on_checkout")));
        foreach ($collection as $profile) {
            if ($profile->getId()) {
                $profile->generate();
            }
        }
    }
}