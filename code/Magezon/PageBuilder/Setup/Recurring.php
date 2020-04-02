<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_PageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\PageBuilder\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class Recurring implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable('catalog_product_entity_text'),
            'value',
            'value',
            [
                'type'    => Table::TYPE_TEXT,
                'length'  => '64M',
                'comment' => 'Value'
            ]
        );
        $setup->getConnection()->changeColumn(
            $setup->getTable('catalog_category_entity_text'),
            'value',
            'value',
            [
                'type'    => Table::TYPE_TEXT,
                'length'  => '64M',
                'comment' => 'Value'
            ]
        );
    }
}
