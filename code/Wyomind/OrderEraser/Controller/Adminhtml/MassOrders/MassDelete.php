<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderEraser\Controller\Adminhtml\MassOrders;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class MassDelete extends \Wyomind\OrderEraser\Controller\Adminhtml\MassOrders
{
    /**
     * @param AbstractCollection $collection
     */
    protected function massAction(AbstractCollection $collection)
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

        $countDeleteOrder = 0;
        
        if (count($ids) > 0) {
            $oeCollection = $this->oeCollectionFactory->create();
            foreach ($collection->getItems() as $order) {
                $oeCollection->deleteOrder($order->getId());
                $countDeleteOrder++;
            }

            $this->messageManager->addSuccess($countDeleteOrder . __(' order(s) successfully deleted'));
        } else {
            $this->messageManager->addError(__('Unable to delete orders.'));
        }
        
        return $this->resultRedirectFactory->create()->setPath($path, array());
    }
}
