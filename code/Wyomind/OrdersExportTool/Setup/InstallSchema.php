<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Module schema installation
 */
class InstallSchema implements InstallSchemaInterface
{
    protected $_dataHelperFactory = null;

    /**
     * InstallSchema constructor.
     * @param \Wyomind\OrdersExportTool\Helper\DataFactory $dataHelperFactory
     */
    public function __construct(
        \Wyomind\OrdersExportTool\Helper\DataFactory $dataHelperFactory 
    )
    {
        $this->_dataHelperFactory = $dataHelperFactory;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        unset($context);

        $installer = $setup;
        $installer->startSetup();
        
        $dataHelper = $this->_dataHelperFactory->create();

        $installer->getConnection()->dropTable($installer->getTable('ordersexporttool_profiles'));
        // drop if exists
        $installer->getConnection()->dropTable($installer->getTable('ordersexporttool_variables'));
        $installer->getConnection()->dropTable($installer->getTable('ordersexporttool_functions'));

        $installer->getConnection($dataHelper->getConnection('sales'))->addColumn(
            $installer->getTable('sales_order'), 'exported_to',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'comment' => 'Exported to flag']
        );
        $installer->getConnection($dataHelper->getConnection('sales'))->addColumn(
            $installer->getTable('sales_order_grid'), 'exported_to',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'comment' => 'Exported to flag']
        );
        $installer->getConnection($dataHelper->getConnection('sales'))->addColumn(
            $installer->getTable('sales_order_item'), 'export_to',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 11, 'nullable' => true, 'comment' => 'Export to flag']

        );
        $installer->getConnection($dataHelper->getConnection('checkout'))->addColumn(
            $installer->getTable('quote_item'), 'export_to',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 11, 'nullable' => true, 'comment' => 'Export to flag']
        );

        $oetTable = $installer->getConnection()
            ->newTable($installer->getTable('ordersexporttool_profiles'))
            ->addColumn(
                'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ], 'Profile ID'
            )
            ->addColumn(
                'name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 90, ['nullable' => false], 'Profile Name'
            )
            ->addColumn(
                'type', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 3, ['nullable' => false], 'Profile Type'
            )
            ->addColumn(
                'encoding', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 40, ['nullable' => false], 'Profile Export Encoding'
            )
            ->addColumn(
                'path', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                    'nullable' => false,
                    'default' => "/export/orders/",
                ], 'Profile Export Folder'
            )
            ->addColumn(
                'product_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 150, ['nullable' => true], 'Profile Product Types'
            )
            ->addColumn(
                'store_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Profile Store IDs'
            )
            ->addColumn(
                'flag', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Profile Flag'
            )
            ->addColumn(
                'single_export', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Profile Single Export'
            )
            ->addColumn(
                'updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'Profile Update Time'
            )
            ->addColumn(
                'last_exported_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [
                    'nullable' => true,
                    'default' => '0',
                ], 'Profile Last Exported Order ID'
            )
            ->addColumn(
                'first_exported_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [
                    'nullable' => true,
                    'default' => '0',
                ], 'Profile First Exported Order ID'
            )
            ->addColumn(
                'automatically_update_last_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '1',
                ], 'Automatically register last exported id'
            )
            ->addColumn(
                'update_status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Update Order Status'
            )
            ->addColumn(
                'update_status_to', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => true], 'Update Order with Status ...'
            )
            ->addColumn(
                'update_status_message', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 500, ['nullable' => true], 'Message for update order status'
            )
            ->addColumn(
                'date_format', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, [
                    'nullable' => false,
                    'default' => 'yyyy-mm-dd',
                ], 'Filename format'
            )
            ->addColumn(
                'include_header', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Include header'
            )
            ->addColumn(
                'product_relation', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 12, [
                    'nullable' => false,
                    'default' => 'all',
                ], 'Product Relation'
            )
            ->addColumn(
                'repeat_for_each', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Repeat for each order'
            )
            ->addColumn(
                'repeat_for_each_increment', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Repeat for each order : use increment'
            )
            ->addColumn(
                'order_by', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Order By'
            )
            ->addColumn(
                'order_by_field', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 3, ['nullable' => true], 'Order By : field'
            )
            ->addColumn(
                'incremental_column', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Add Increment for First Column'
            )
            ->addColumn(
                'incremental_column_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, ['nullable' => true], 'Incremental column Name'
            )
            ->addColumn(
                'header', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [], 'Header'
            )
            ->addColumn(
                'extra_header', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [], 'Extra Header'
            )
            ->addColumn(
                'body', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [], 'Body'
            )
            ->addColumn(
                'footer', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [], 'Footer'
            )
            ->addColumn(
                'separator', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 3, ['nullable' => true], 'Field Separator'
            )
            ->addColumn(
                'protector', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 1, ['nullable' => true], 'Field Protector'
            )
            ->addColumn(
                'escaper', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2, ['nullable' => false,
                'default' => "\\"], 'Delimiter escaper'
            )
            ->addColumn(
                'enclose_data', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '1',
                ], 'Enclose xml data'
            )
            ->addColumn(
                'attributes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['nullable' => true], 'Attributes Filters'
            )
            ->addColumn(
                'states', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['nullable' => true], 'States Selection'
            )
            ->addColumn(
                'customer_groups', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['nullable' => true], 'Customer groups Filters'
            )
            ->addColumn(
                'scheduled_task', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 900, ['nullable' => false], 'Customer groups Filters'
            )
            ->addColumn(
                'ftp_enabled', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Use FTP'
            )
            ->addColumn(
                'ftp_host', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 300, ['nullable' => true], 'Ftp Host'
            )
            ->addColumn(
                'ftp_login', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 300, ['nullable' => true], 'Ftp User'
            )
            ->addColumn(
                'ftp_password', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 300, ['nullable' => true], 'Ftp Password'
            )
            ->addColumn(
                'ftp_active', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Use active FTP'
            )
            ->addColumn(
                'ftp_dir', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 300, ['nullable' => true], 'Ftp Dir'
            )
            ->addColumn(
                'use_sftp', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => true,
                    'default' => '0',
                ], 'Use SFTP'
            )
            ->addColumn(
                'mail_enabled', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Send Export by Email'
            )
            ->addColumn(
                'mail_subject', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 200, ['nullable' => true], 'Email Subject'
            )
            ->addColumn(
                'mail_recipients', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 300, ['nullable' => true], 'Email Recipients'
            )
            ->addColumn(
                'mail_message', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['nullable' => true], 'Email Message'
            )
            ->addColumn(
                'mail_zip', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Zip all files in zip'
            )
            ->addColumn(
                'mail_one_report', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Send one email with all files or one email per files'
            )
            ->addColumn(
                'storage_enabled', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '1',
                ], 'Store the files on local server'
            )
            ->addColumn(
                'product_filter', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, [
                    'nullable' => false,
                    'default' => '0',
                ], 'Product Filter'
            )
            ->addIndex(
                $installer->getIdxName('ordersexporttool', ['id']), ['id']
            )
            ->setComment('Orders Export Tool Profiles Table');

        $installer->getConnection()->createTable($oetTable);

        $oetVariablesTable = $installer->getConnection()
            ->newTable($installer->getTable('ordersexporttool_variables'))
            ->addColumn(
                'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ], 'Custom variable ID'
            )
            ->addColumn(
                'scope', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, ['nullable' => false], 'Custom Variable Scope'
            )
            ->addColumn(
                'name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100, ['nullable' => false], 'Custom Variable Name'
            )
            ->addColumn(
                'comment', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['nullable' => true], 'Custom Variable Comment'
            )
            ->addColumn(
                'script', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['nullable' => false], 'Custom Variable Script'
            )
            ->addIndex(
                $installer->getIdxName('ordersexporttool_variables', ['id']), ['id']
            )
            ->setComment('Orders Export Tool Custom Variables Table');

        $installer->getConnection()->createTable($oetVariablesTable);

        $oetFunctionsTable = $installer->getConnection()
            ->newTable($installer->getTable('ordersexporttool_functions'))
            ->addColumn(
                'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ], 'Custom function ID'
            )
            ->addColumn(
                'script', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [], 'Custom Function Script'
            )
            ->addIndex(
                $installer->getIdxName('ordersexporttool_functions', ['id']), ['id']
            )
            ->setComment('Orders Export Tool Custom Functions Table');

        $installer->getConnection()->createTable($oetFunctionsTable);

        $installer->endSetup();
    }
}