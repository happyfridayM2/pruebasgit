<?php
namespace Magebees\Products\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cws_product_exported_file'))
            ->addColumn(
                'export_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true,'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Export Id'
            )
            ->addColumn('file_name', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('exported_file_date_times', Table::TYPE_DATETIME, null, ['nullable' => true, 'default' => null]);
        $installer->getConnection()->createTable($table);
        $table = $installer->getConnection()
                ->newTable($installer->getTable('cws_product_import_log'))
                ->addColumn(
                    'log_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true,'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Log Id'
                )
                ->addColumn('error_type', Table::TYPE_INTEGER, 11, ['nullable' => false])
                ->addColumn('product_sku', Table::TYPE_TEXT, 100, ['nullable' => false])
                ->addColumn('error_information', Table::TYPE_TEXT, '2M', ['nullable' => false]);
        $installer->getConnection()->createTable($table);
        $table = $installer->getConnection()
                ->newTable($installer->getTable('cws_product_import_profiler'))
                ->addColumn(
                    'profiler_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true,'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Profiler Id'
                )
                ->addColumn('bypass_import', Table::TYPE_SMALLINT, 1, ['nullable' => false])
                ->addColumn('validate', Table::TYPE_SMALLINT, 1, ['nullable' => false])
                ->addColumn('imported', Table::TYPE_SMALLINT, 1, ['nullable' => false])
                ->addColumn('product_data', Table::TYPE_TEXT, '2M', ['nullable' => false]);
        $installer->getConnection()->createTable($table);
        $table = $installer->getConnection()
                ->newTable($installer->getTable('cws_product_validation_log'))
                ->addColumn(
                    'log_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true,'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Log Id'
                )
                ->addColumn('error_type', Table::TYPE_INTEGER, 11, ['nullable' => false])
                ->addColumn('product_sku', Table::TYPE_TEXT, 100, ['nullable' => false])
                ->addColumn('error_information', Table::TYPE_TEXT, '2M', ['nullable' => false]);
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
