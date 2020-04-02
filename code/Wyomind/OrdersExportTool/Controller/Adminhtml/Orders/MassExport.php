<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Orders;

class MassExport extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    public $collectionFactory = null;
    
    /**
     * @var \Wyomind\OrdersExportTool\Model\Profiles
     */
    protected $_profilesModel = null;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Wyomind\OrdersExportTool\Model\Profiles $profilesModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context, 
        \Magento\Ui\Component\MassAction\Filter $filter, 
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Wyomind\OrdersExportTool\Model\Profiles $profilesModel
    )
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->_profilesModel = $profilesModel;
    }

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @return mixed
     */
    protected function massAction(\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection)
    {
        $path = 'sales/order/index';
        $ids = [];
        $profileIds = [];
        
        foreach ($collection->getItems() as $order) {
            $ids[] = $order->getId();
            foreach ($order->getAllItems() as $item) {
                if ($item->getExportTo() > 0) {
                    $profileIds[] = $item->getExportTo();
                }
            }
        }
        
        try {
            if (is_array($profileIds)) {
                $profileCollection = $this->_profilesModel->getCollection()->searchProfiles($profileIds);
                foreach ($profileCollection as $profile) {
                    $this->getRequest()->setParam('id', $profile->getId());
                    $this->getRequest()->setParam('attributes', '[{"line":"11","checked":true,"code":"order_item.export_to","condition":"eq","value":"' . $profile->getId() . '"},{"line":"12","checked":true,"code":"order.entity_id","condition":"in","value":"' . implode(',', $ids) . '"}]');
                    $profile->generate($this->getRequest());
                }
            } else {
                $this->messageManager->addWarning(__('No data to export'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError('Error : ' . $e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath($path, []);
    }
    
    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_OrdersExportTool::orders');
    }
}