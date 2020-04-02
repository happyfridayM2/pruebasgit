<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Orders;

/**
 * Reset action
 */
class Reset extends AbstractOrders
{
    /**
     * Execute action
     */
    public function execute()
    {
        $order = $this->_orderRepository->get($this->getRequest()->getParam('order'));

        $flags = explode(',', $order->getExportedTo());
        $flagToRemove = $this->getRequest()->getParam('profile');
        unset($flags[array_search($flagToRemove, $flags)]);

        // save order
        $order->setExportedTo(implode(',', $flags));
        $order->save();

        try {
            // save order grid
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $appResource = $om->get('\Magento\Framework\App\ResourceConnection');
            $connection = $appResource->getConnection($this->helperData->getConnection('sales'));
            $tableSog = $appResource->getTableName('sales_order_grid');
            $connection->update($tableSog, ['exported_to' => implode(',', $flags)], "entity_id = '" . $order->getId() . "'");
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}