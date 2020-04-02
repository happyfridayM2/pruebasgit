<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Orders;

/**
 * Update action
 */
class Update extends AbstractOrders
{
    /**
     * Execute action
     */
    public function execute()
    {
        try {
            $id = $this->getRequest()->getPost('item_id');
            $model = $this->_orderItem->load($id);
            $model->setExportTo($this->getRequest()->getPost('value'));
            $model->save();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Error : ') . $e->getMessage());
        }
    }
}