<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Setup;

use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Attribute installation
 */
class ProductSetup extends EavSetup
{
    protected $_productFactory;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface                    $setup
     * @param \Magento\Eav\Model\Entity\Setup\Context                              $context
     * @param \Magento\Framework\App\CacheInterface                                $cache
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $attrGroupCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory                                $productFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory,
        ProductFactory $productFactory
    )
    {
        $this->_productFactory = $productFactory;
        parent::__construct($setup, $context, $cache, $attrGroupCollectionFactory);
    }

    public function getDefaultEntities()
    {
        return [
            'catalog_product' => [
                'entity_model' => 'Magento\Catalog\Model\ResourceModel\Product',
                'attribute_model' => 'Magento\Catalog\Model\ResourceModel\Eav\Attribute',
                'table' => 'catalog_product_entity',
                'additional_attribute_table' => 'catalog_eav_attribute',
                'entity_attribute_collection' => 'Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection',
                'attributes' => [
                    'export_to' => [
                        'group' => "Order export",
                        'label' => 'Export by default to',
                        'type' => 'int',
                        'input' => 'select',
                        'default' => '1',
                        'note' => 'Export by default this product with the above export profile',
                        'backend' => '',
                        'frontend' => '',
                        'source' => 'Wyomind\OrdersExportTool\Model\Attribute\Source\Export',
                        'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => true,
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'visible_in_advanced_search' => false,
                        'unique' => false
                    ]
                ]
            ]
        ];
    }
}