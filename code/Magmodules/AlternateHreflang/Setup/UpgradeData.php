<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeData implements UpgradeDataInterface
{

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), "1.1.0", "<")) {
            $setup->getConnection()->addColumn(
                $setup->getTable('cms_page'),
                'alternate_category',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '255',
                    'nullable' => true,
                    'comment'  => 'Magmodules AlternateHreflang Category'
                ]
            );
        }
        $setup->endSetup();
    }
}
