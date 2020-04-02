<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Orders;

/**
 * Export action
 */
class Export extends AbstractOrders
{
    /**
     * Execute action
     */
    public function execute()
    {
        $path = 'sales/order/index';
        $params = [];
        if ($this->getRequest()->getParam('order_ids')) {
            $orderIds = [$this->getRequest()->getParam('order_ids')];
            $params = ["order_id" => $this->getRequest()->getParam('order_ids')];
        } else {
            if ($this->getRequest()->getParam('selected')) {
                $orderIds = $this->getRequest()->getParam('selected');
            } else {
                $orderIds = [];
                foreach ($this->_orderFactory->create() as $order) {
                    $orderIds[] = $order->getId();
                }
            }
        }

        $profileIds = [];

        $filterGroup = $this->_objectManager->create('\Magento\Framework\Api\Search\FilterGroup');
        $filter = $this->_objectManager->create('\Magento\Framework\Api\Filter');
        $filter->setField('entity_id');
        $filter->setConditionType('in');
        $filter->setValue($orderIds);
        $filterGroup->setFilters([$filter]);

        $searchCriteria = $this->_objectManager->create('\Magento\Framework\Api\SearchCriteria');
        $searchCriteria->setFilterGroups([$filterGroup]);

        $collection = $this->_orderRepository->getList($searchCriteria);
        foreach ($collection->getItems() as $order) {
            foreach ($order->getAllItems() as $item) {
                if ($item->getExportTo() > 0) {
                    $profileIds[] = $item->getExportTo();
                }
            }
        }

        try {
            if (is_array($profileIds)) {
                $collection = $this->_profilesModel->getCollection()->searchProfiles($profileIds);
                foreach ($collection as $profile) {
                    $this->getRequest()->setParam('id', $profile->getId());
                    $this->getRequest()->setParam('attributes', '[{"line":"11","checked":true,"code":"order_item.export_to","condition":"eq","value":"' . $profile->getId() . '"},{"line":"12","checked":true,"code":"order.entity_id","condition":"in","value":"' . implode(',', $orderIds) . '"}]');
                    $profile->generate($this->getRequest());
                }
            } else {
                $this->messageManager->addWarning(__('No data to export'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError('Error : ' . $e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath($path, $params);
    }
}