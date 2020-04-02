<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Amasty\Conf\Helper\Data;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.3.0', '<')) {
            $this->addPreselectAttribute($setup);
            $this->createFlipperAttribute($setup);
        }
        if (version_compare($context->getVersion(), '2.3.9', '<')) {
            $this->removeBackendModelForPreselect($setup);
        }

        if (version_compare($context->getVersion(), '2.4.0', '<')) {
            $this->addMatrixAttribute($setup);
        }

        if (version_compare($context->getVersion(), '2.5.4', '<')) {
            $this->hidePreselectAttribute($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function removeBackendModelForPreselect($setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->updateAttribute(
            Product::ENTITY,
            Data::PRESELECT_ATTRIBUTE,
            'backend_model',
            null
        );
    }

    /**
     * Add attribute for save preselect sku
     * @param ModuleDataSetupInterface $setup
     */
    private function addPreselectAttribute($setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            Data::PRESELECT_ATTRIBUTE,
            [
                'type'                      => 'text',
                'frontend'                  => '',
                'label'                     => 'Simple Preselect',
                'input'                     => 'text',
                'class'                     => '',
                'source'                    => '',
                'global'                    => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible'                   => true,
                'used_in_product_listing'   => true,
                'required'                  => false,
                'user_defined'              => true,
                'default'                   => '',
                'searchable'                => false,
                'filterable'                => false,
                'comparable'                => false,
                'visible_on_front'          => false,
                'unique'                    => false,
                'apply_to'                  => 'configurable',
                'group'                     => 'configurable'
            ]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function addMatrixAttribute($setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            Data::MATRIX_ATTRIBUTE,
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Display Last Attribute in Rows',
                'input' => 'boolean',
                'used_in_product_listing'   => true,
                'class' => '',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'apply_to' => 'configurable'
            ]
        );

        $matrixAttribute = $eavSetup->getAttributeId(
            Product::ENTITY,
            Data::MATRIX_ATTRIBUTE
        );

        foreach ($eavSetup->getAllAttributeSetIds(Product::ENTITY) as $attributeSetId) {
            try {
                $attributeGroupId = $eavSetup->getAttributeGroupId(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attributeSetId,
                    'General'
                );
            } catch (\Exception $e) {
                $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attributeSetId
                );
            }

            /*add attribute to attribute set*/
            $eavSetup->addAttributeToSet(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeSetId,
                $attributeGroupId,
                $matrixAttribute
            );
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function createFlipperAttribute(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        /**
         * Install eav entity types to the eav/entity_type table
         */
        $eavSetup->addAttribute(
            'catalog_product',
            \Amasty\Conf\Helper\Data::FLIPPER_IMAGE_ID,
            [
                'type' => 'varchar',
                'label' => 'Flipper Image',
                'input' => 'media_image',
                'frontend' => \Magento\Catalog\Model\Product\Attribute\Frontend\Image::class,
                'required' => false,
                'sort_order' => 4,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true
            ]
        );
    }

    /**
     * Attribute must showing manually
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function hidePreselectAttribute(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $preselectAttrId = $eavSetup->getAttributeId(
            Product::ENTITY,
            Data::PRESELECT_ATTRIBUTE
        );

        $setup->updateTableRow(
            'catalog_eav_attribute',
            'attribute_id',
            $preselectAttrId,
            'is_visible',
            0
        );
    }
}
