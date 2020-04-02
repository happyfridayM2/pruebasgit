<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderEraser\Controller\Adminhtml\Orders;

class Delete extends \Wyomind\OrderEraser\Controller\Adminhtml\Orders
{
    public function execute()
    {
        $path = "sales/order/index";

        $params = $this->getRequest()->getParams();

        $ids = array();

        if (isset($params['excluded']) && $params['excluded'] == "false") {
            $ids[] = '*';
        } else {
            if (isset($params['selected'])) {
                if (is_array($params['selected'])) {
                    $ids = $params['selected'];
                } else {
                    $ids = array($params['selected']);
                }
            }
        }
        
        $filters = [];
        if (isset($params['filters'])) {
            $filters = $params['filters'];
        }

        $countDeleteOrder = 0;
        if (count($ids) > 0) {
            $collection = $this->ordersCollectionFactory->create();
            if ($ids[0] == '*') {
                
                if (!empty($filters)) {
                    $searchCriteria = $this->searchCriteriaBuilder;
                    foreach ($filters as $attribute => $values) {
                        if($attribute != "placeholder") {
                            if(!is_array($values)) {
                                $searchCriteria->addFilter($attribute, $values, 'eq');
                            } elseif (isset($values["from"])) {
                                $searchCriteria->addFilter($attribute, date('Y-m-d 00:00:00', strtotime($values['from'])), 'gteq');
                                $searchCriteria->addFilter($attribute, date('Y-m-d 23:59:59', strtotime($values['from'])), 'lteq');
                            }
                        }
                    }
                    $orders = $this->orderRepository->getList($searchCriteria->create());
                    
                    
                    foreach ($orders as $order) {
                        $collection->deleteOrder($order->getId());
                        $countDeleteOrder++;
                    }
                    $this->messageManager->addSuccess($countDeleteOrder . __(' order(s) successfully deleted'));
                } else { // no filters
                    $countDeleteOrder = $collection->deleteAll();
                    $this->messageManager->addSuccess($countDeleteOrder . __('All orders have been successfully deleted'));
                }
            } else {
                foreach ($ids as $id) {
                    $collection->deleteOrder($id);
                    $countDeleteOrder++;
                }
                $this->messageManager->addSuccess($countDeleteOrder . __(' order(s) successfully deleted'));
            }
        } else {
            $this->messageManager->addError(__('Unable to delete orders.'));
        }

        return $this->resultRedirectFactory->create()->setPath($path, array());
    }
}