<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup\Operation;

use Amasty\Feed\Model\Category\ResourceModel\Taxonomy;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeTo210
 */
class UpgradeTo210
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();

        $table = $connection->newTable(
            $setup->getTable(Taxonomy::TABLE_NAME)
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'category',
            Table::TYPE_TEXT,
            null,
            [],
            'Category'
        )->addColumn(
            'language_code',
            Table::TYPE_TEXT,
            null,
            [],
            'Language Code'
        );

        $setup->getConnection()->createTable($table);
    }
}
