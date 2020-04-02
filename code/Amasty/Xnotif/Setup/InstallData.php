<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */

namespace Amasty\Xnotif\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'amxnotif_hide_alert',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Hide Stock Alert Block',
                'input' => 'boolean',
                'used_in_product_listing'   => true,
                'class' => '',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => ''
            ]
        );
        $attributeId = $eavSetup->getAttributeId(
            Product::ENTITY,
            'amxnotif_hide_alert'
        );

        $attributeSets = $eavSetup->getAllAttributeSetIds(
            Product::ENTITY
        );
        foreach ($attributeSets as $attributeSetId) {
            try {
                $attributeGroupId = $eavSetup->getAttributeGroupId(
                    Product::ENTITY,
                    $attributeSetId,
                    'General'
                );
            } catch (\Exception $e) {
                $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(
                    Product::ENTITY,
                    $attributeSetId
                );
            }
            $eavSetup->addAttributeToSet(
                Product::ENTITY,
                $attributeSetId,
                $attributeGroupId,
                $attributeId
            );
        }

        $installer = $setup;
        $tableName = $installer->getTable(
            'core_config_data'
        );

        $cols = $installer->getConnection()->fetchCol(
            $installer->getConnection()->select()
                ->from($tableName)
                ->where('path = ?', 'catalog/productalert/allow_stock')
        );
        if ($cols) {
            $installer->getConnection()->update(
                $tableName,
                ['value' => 1],
                'path = \'catalog/productalert/allow_stock\''
            );
        } else {
            $installer->getConnection()->insert(
                $tableName,
                [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => 'catalog/productalert/allow_stock',
                    'value' => '1'
                ]
            );
        }
    }
}
