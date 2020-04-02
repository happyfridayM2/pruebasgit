<?php
namespace Magebees\Navigationmenu\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
		
		 $tableName = $installer->getTable('magebees_menucreatorgroup');
		if ($installer->getConnection()->isTableExists($tableName) != true) {
					
					
        $table = $installer->getConnection()->newTable(

            $installer->getTable('magebees_menucreatorgroup')
        )->addColumn(
            'group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'group_id'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'title'
        )
          ->addColumn(
              'showhidetitle',
              \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
              6,
              ['nullable' => false, 'default' => '1'],
              'showhidetitle'
          )->addColumn(
              'description',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              null,
              ['nullable' => false],
              'description'
          )->addColumn(
              'status',
              \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
              6,
              ['nullable' => false, 'default' => '1'],
              'status'
          )->addColumn(
              'menutype',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => false],
              'menutype'
          )
          ->addColumn(
              'alignment',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => false, 'default' => 'left'],
              'alignment'
          )->addColumn(
              'level',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => false],
              'level'
          )->addColumn(
              'position',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['default' => '1'],
              'position'
          )->addColumn(
              'created_time',
              \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
              null,
              ['nullable' =>true],
              'created_time'
          )->addColumn(
              'update_time',
              \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
              null,
              ['nullable' =>true],
              'update_time'
          )->addColumn(
              'show_hide_title',
              \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
              6,
              ['nullable' => false, 'default' => '1'],
              'show_hide_title'
          )->addColumn(
              'image_height',
              \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
              6,
              ['nullable' => false, 'default' => '25'],
              'image_height'
          )->addColumn(
              'image_width',
              \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
              6,
              ['nullable' => false, 'default' => '25'],
              'image_width'
          )
          ->addColumn(
              'direction',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              10,
              ['nullable' => true],
              'direction'
          )
          ->addColumn(
              'rootoptions',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              256,
              ['nullable' => true],
              'rootoptions'
          )->addColumn(
              'megaoptions',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              256,
              ['nullable' => true],
              'megaoptions'
          )->addColumn(
              'suboptions',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              256,
              ['nullable' => true],
              'suboptions'
          )->addColumn(
              'flyoptions',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              256,
              ['nullable' => true],
              'flyoptions'
          )->addColumn(
			'unique_code', 
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255,
			['nullable' => true,
			 'default' => null]);
        $installer->getConnection()->createTable($table);
		}
		$tableName = $installer->getTable('magebees_menucreator');
		if ($installer->getConnection()->isTableExists($tableName) != true) {
        $table = $installer->getConnection()
          ->newTable($installer->getTable('magebees_menucreator'))
          ->addColumn(
              'menu_id',
              \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
              null,
              ['identity' => true, 'unsigned' => true,  'nullable' => false, 'primary' => true],
              'menu_id'
          )
          ->addColumn(
              'group_id',
              \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
              null,
              ['unsigned' => true, 'nullable' => false],
              'group_id'
          )
          ->addColumn(
              'title',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => false, 'default' =>null],
              'title'
          )->addColumn(
              'description',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              null,
              ['nullable' => false],
              'description'
          )->addColumn(
              'image',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              null,
              ['nullable' => false],
              'image'
          )->addColumn(
              'type',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              null,
              ['nullable' => false],
              'type'
          )->addColumn(
              'category_id',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              6,
              ['nullable' => false, 'default' =>null],
              'category_id'
          )->addColumn(
              'cmspage_identifier',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => false, 'default' =>null],
              'cmspage_identifier'
          )->addColumn(
              'staticblock_identifier',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => false, 'default' =>null],
              'staticblock_identifier'
          )->addColumn(
              'product_id',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              6,
              ['nullable' => false, 'default' =>null],
              'product_id'
          )->addColumn(
              'parent_id',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              6,
              ['nullable' => false, 'default' =>null],
              'parent_id'
          )->addColumn(
              'url_value',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => false, 'default' => null],
              'url_value'
          )->addColumn(
              'usedlink_identifier',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => false, 'default' =>null],
              'usedlink_identifier'
          )->addColumn(
              'image_status',
              \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
              6,
              ['nullable' => false, 'default' => '1'],
              'image_status'
          )->addColumn(
              'show_category_image',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              [],
              'show_category_image'
          )->addColumn(
              'show_custom_category_image',
              \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
              6,
              [],
              'show_custom_category_image'
          )->addColumn(
              'position',
              \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
              6,
              ['nullable' => false, 'default' => '-1'],
              'position'
          )->addColumn(
              'class_subfix',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              null,
              ['nullable' => false],
              'class_subfix'
          )->addColumn(
              'permission',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => false,'default' => '-1'],
              'permission'
          )->addColumn(
              'status',
              \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
              6,
              ['nullable' => false, 'default' => '1'],
              'status'
          )->addColumn(
              'created_time',
              \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
              6,
              ['nullable' => true],
              'created_time'
          )->addColumn(
              'update_time',
              \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
              6,
              ['nullable' => true],
              'update_time'
          )->addColumn(
              'target',
              \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
              6,
              ['default' => '1'],
              'target'
          )->addColumn(
              'storeids',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              250,
              [],
              'storeids'
          )->addColumn(
              'autosub',
              \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
              6,
              [],
              'autosub'
          )->addColumn(
              'use_category_title',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              5,
              ['default' => '2'],
              'use_category_title'
          )->addColumn(
              'autosubimage',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              5,
              ['default' => '0'],
              'autosubimage'
          )->addColumn(
              'text_align',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['default' => 'left'],
              'text_align'
          )->addColumn(
              'image_type',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['default' => 'none'],
              'image_type'
          )->addColumn(
              'subcolumnlayout',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => true],
              'subcolumnlayout'
          )->addColumn(
              'title_show_hide',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => true],
              'title_show_hide'
          )->addColumn(
              'useexternalurl',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => true],
              'useexternalurl'
          )->addColumn(
              'setrel',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => true],
              'setrel'
          )->addColumn(
              'label_show_hide',
              \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
              2,
              ['nullable' => true],
              'label_show_hide'
          )->addColumn(
              'label_title',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => true],
              'label_title'
          )->addColumn(
              'label_height',
              \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
              3,
              ['nullable' => true],
              'label_height'
          )->addColumn(
              'label_width',
              \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
              3,
              ['nullable' => true],
              'label_width'
          )->addColumn(
              'label_bg_color',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => true],
              'label_bg_color'
          )->addColumn(
              'label_color',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              ['nullable' => true],
              'label_color'
          );
        $installer->getConnection()->createTable($table);
		}
        $installer->endSetup();
    }
}
