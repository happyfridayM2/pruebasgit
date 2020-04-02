<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\MoveToPreselect
     */
    private $moveToPreselect;

    /**
     * @var Operation\MoveToOutOfOtock
     */
    private $moveToOutOfOtock;

    public function __construct(
        \Amasty\Conf\Setup\Operation\MoveToPreselect $moveToPreselect,
        \Amasty\Conf\Setup\Operation\MoveToOutOfOtock $moveToOutOfOtock
    ) {
        $this->moveToPreselect = $moveToPreselect;
        $this->moveToOutOfOtock = $moveToOutOfOtock;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->addGroupsAttribute($setup);
        }

        if (version_compare($context->getVersion(), '2.3.8', '<')) {
            $this->removeGroupAliase($setup);
        }

        if (version_compare($context->getVersion(), '2.5.2', '<')) {
            $this->moveToPreselect->execute($setup);
            $this->moveToOutOfOtock->execute($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public function addGroupsAttribute(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('amasty_conf_group_attr');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'group_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'attribute_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'unsigned' => true]
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false,
                    'default' => false]
            )
            ->addColumn(
                'group_code',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => false]
            )
            ->addColumn(
                'url',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => false]
            )
            ->addColumn(
                'position',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                'visual',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => false]
            )
            ->addColumn(
                'type',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                'enabled',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => false]
            )
            ->addIndex(
                $setup->getIdxName(
                    'amasty_conf_group_attr',
                    ['attribute_id', 'group_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['attribute_id', 'group_code'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $setup->getFkName(
                    'amasty_conf_group_attr',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $setup->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $setup->getConnection()->createTable($table);

        $tableName = $setup->getTable('amasty_conf_group_attr_option');
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'group_option_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'group_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                'option_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Option ID'
            )->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort Order'
            )->addIndex(
                $setup->getIdxName(
                    'amasty_conf_group_attr_option',
                    ['group_id', 'option_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['group_id', 'option_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $setup->getFkName(
                    'amasty_conf_group_attr_option',
                    'group_id',
                    'amasty_conf_group_attr',
                    'group_id'
                ),
                'group_id',
                $setup->getTable('amasty_conf_group_attr'),
                'group_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    'amasty_conf_group_attr_option',
                    'option_id',
                    'eav_attribute_option',
                    'option_id'
                ),
                'option_id',
                $setup->getTable('eav_attribute_option'),
                'option_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public function removeGroupAliase(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('amasty_conf_group_attr');

        $setup->getConnection()->dropColumn($tableName, 'url');
    }

}
