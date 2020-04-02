<?php

/**
 * Created by PhpStorm.
 * User: migue
 * Date: 22/05/2017
 * Time: 16:06
 */

namespace Oct8ne\Oct8ne\Setup;

use \Magento\Framework\Setup\InstallSchemaInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\DB\Ddl\Table as Type;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;

        $installer->startSetup();

        if (!$installer->tableExists('oct8nehistory')) {

            $table = $installer->getConnection()->newTable($installer->getTable('oct8nehistory'));

            $table->addColumn('id_oct8nehistory', Type::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]);
            $table->addColumn('cart_id', Type::TYPE_INTEGER, null, ['identity' => false, 'unsigned' => true, 'nullable' => false]);
            $table->addColumn('session_id', Type::TYPE_TEXT, null, ['identity' => false, 'nullable' => false]);
            $table->addColumn('creation_time', Type::TYPE_DATETIME, null, ['identity' => false, 'nullable' => false]);
            $table->addColumn('update_time', Type::TYPE_DATETIME, null, ['identity' => false, 'nullable' => false]);
            $table->addIndex("cart_id", ["cart_id"]);

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }

}

/**
 * Adds column to table.
 *
 * $options contains additional options for columns. Supported values are:
 * - 'unsigned', for number types only. Default: FALSE.
 * - 'precision', for numeric and decimal only. Default: taken from $size, if not set there then 0.
 * - 'scale', for numeric and decimal only. Default: taken from $size, if not set there then 10.
 * - 'default'. Default: not set.
 * - 'nullable'. Default: TRUE.
 * - 'primary', add column to primary index. Default: do not add.
 * - 'primary_position', only for column in primary index. Default: count of primary columns + 1.
 * - 'identity' or 'auto_increment'. Default: FALSE.
 *
 * **/