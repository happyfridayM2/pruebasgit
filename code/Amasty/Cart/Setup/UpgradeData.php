<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Setup;

use Amasty\Cart\Setup\Operation\UpgradeSettings;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var UpgradeSettings
     */
    private $upgradeSettings;

    public function __construct(
        UpgradeSettings $upgradeSettings
    ) {
        $this->upgradeSettings = $upgradeSettings;
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

        if (version_compare($context->getVersion(), '1.7.0', '<')) {
            $this->upgradeSettings->execute($setup);
        }

        $setup->endSetup();
    }
}
