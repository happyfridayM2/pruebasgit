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

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
      private $eavSetupFactory;

      /**
       * @param BrandFactory $brandFactory 
       * @param GroupFactory $groupFactory 
       */
      public function __construct(
            EavSetupFactory $eavSetupFactory
            )
      {
            $this->eavSetupFactory = $eavSetupFactory;
      }

      public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
      {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]); 
            if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'featured')) {
                  $eavSetup->addAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        'featured',
                        [
                              'group'                         => 'General',
                              'type'                          => 'int',
                              'input'                         => 'boolean',
                              'default'                       => 1,
                              'label'                         => 'Featured',
                              'backend'                       => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                              'frontend'                      => '',
                              'source'                        => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                              'visible'                       => 1,
                              'user_defined'                  => 1,
                              'used_for_price_rules'          => 1,
                              'position'                      => 2,
                              'unique'                        => 0,
                              'sort_order'                    => 100,
                              'is_global'                     => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                              'is_configurable'               => 1,
                              'is_searchable'                 => 0,
                              'is_required'                   => 0,
                              'required'                      => 0,
                              'is_visible_in_advanced_search' => 0,
                              'is_comparable'                 => 0,
                              'is_filterable'                 => 0,
                              'is_filterable_in_search'       => 1,
                              'is_used_for_promo_rules'       => 1,
                              'is_html_allowed_on_front'      => 0,
                              'is_visible_on_front'           => 1,
                              'used_in_product_listing'       => 1,
                              'used_for_sort_by'              => 0,
                        ]
                  );
            }
      }

}