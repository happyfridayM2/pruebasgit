<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderEraser\Model\ResourceModel\Orders;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Order', 'Magento\Sales\Model\ResourceModel\Order');
    }

    public function deleteOrder($orderId)
    {

        $connection = $this->getConnection('write');
        
        $tableSo = $this->getTable('sales_order');
        $tableSog = $this->getTable('sales_order_grid');
        $tableScm = $this->getTable('sales_creditmemo');
        $tableScmg = $this->getTable('sales_creditmemo_grid');
        $tableScmc = $this->getTable('sales_creditmemo_comment');
        $tableScmi = $this->getTable('sales_creditmemo_item');
        $tableSi = $this->getTable('sales_invoice');
        $tableSig = $this->getTable('sales_invoice_grid');
        $tableSic = $this->getTable('sales_invoice_comment');
        $tableSii = $this->getTable('sales_invoice_item');
        $tableSoi = $this->getTable('sales_order_item');
        $tableSop = $this->getTable('sales_order_payment');
        $tableSot = $this->getTable('sales_order_tax');
        $tableSoti = $this->getTable('sales_order_tax_item');
        $tableSos = $this->getTable('sales_shipment');
        $tableSosg = $this->getTable('sales_shipment_grid');
        $tableSosc = $this->getTable('sales_shipment_comment');
        $tableSosi = $this->getTable('sales_shipment_item');

        // credits memos
        $connection->delete($tableScmc, "parent_id in (SELECT entity_id FROM " . $tableScm . " WHERE order_id = " . $orderId. ")");
        $connection->delete($tableScmi, "parent_id in (SELECT entity_id FROM " . $tableScm . " WHERE order_id = " . $orderId. ")");
        $connection->delete($tableScm, "order_id = " . $orderId);
        $connection->delete($tableScmg, "order_id = " . $orderId);
        
        // invoices
        $connection->delete($tableSic, "parent_id in (SELECT entity_id FROM " . $tableSi . " WHERE order_id = " . $orderId. ")");
        $connection->delete($tableSii, "parent_id in (SELECT entity_id FROM " . $tableSi . " WHERE order_id = " . $orderId. ")");
        $connection->delete($tableSi, "order_id = " . $orderId);
        $connection->delete($tableSig, "order_id = " . $orderId);
        
        // items
        $connection->delete($tableSoi, "order_id = " . $orderId);
        
        // payments
        $connection->delete($tableSop, "parent_id = " . $orderId);
        // tax
        $connection->delete($tableSoti, "tax_id in (SELECT tax_id FROM " . $tableSot . " WHERE order_id = " . $orderId. ")");
        $connection->delete($tableSot, "order_id = " . $orderId);
        
        // shipments
        $connection->delete($tableSosc, "parent_id in (SELECT order_id FROM " . $tableSos . " WHERE order_id = " . $orderId. ")");
        $connection->delete($tableSosi, "parent_id in (SELECT order_id FROM " . $tableSos . " WHERE order_id = " . $orderId. ")");
        $connection->delete($tableSos, "order_id = " . $orderId);
        $connection->delete($tableSosg, "order_id = " . $orderId);
        
        // orders
        $connection->delete($tableSo, "entity_id = " . $orderId);
        $connection->delete($tableSog, "entity_id = " . $orderId);
    }

    public function deleteAll()
    {

        $connection = $this->getConnection('write');
        
        $tableSo = $connection->getTable('sales_order');
        $tableSog = $connection->getTable('sales_order_grid');
        $tableScm = $connection->getTable('sales_creditmemo');
        $tableScmg = $connection->getTable('sales_creditmemo_grid');
        $tableScmc = $connection->getTable('sales_creditmemo_comment');
        $tableScmi = $connection->getTable('sales_creditmemo_item');
        $tableSi = $connection->getTable('sales_invoice');
        $tableSig = $connection->getTable('sales_invoice_grid');
        $tableSic = $connection->getTable('sales_invoice_comment');
        $tableSii = $connection->getTable('sales_invoice_item');
        $tableSoi = $connection->getTable('sales_order_item');
        $tableSop = $connection->getTable('sales_order_payment');
        $tableSot = $connection->getTable('sales_order_tax');
        $tableSoti = $connection->getTable('sales_order_tax_item');
        $tableSos = $connection->getTable('sales_shipment');
        $tableSosg = $connection->getTable('sales_shipment_grid');
        $tableSosc = $connection->getTable('sales_shipment_comment');
        $tableSosi = $connection->getTable('sales_shipment_item');

        // credits memos
        $connection->delete($tableScmc, "");
        $connection->delete($tableScmi, "");
        $connection->delete($tableScm, "");
        $connection->delete($tableScmg, "");
        
        // invoices
        $connection->delete($tableSic, "");
        $connection->delete($tableSii, "");
        $connection->delete($tableSi, "");
        $connection->delete($tableSig, "");
        
        // items
        $connection->delete($tableSoi, "");
        
        // payments
        $connection->delete($tableSop, "");
        // tax
        $connection->delete($tableSoti, "");
        $connection->delete($tableSot, "");
        
        // shipments
        $connection->delete($tableSosc, "");
        $connection->delete($tableSosi, "");
        $connection->delete($tableSos, "");
        $connection->delete($tableSosg, "");
        
        // orders
        $connection->delete($tableSo, "");
        $connection->delete($tableSog, "");
        
    }
}
