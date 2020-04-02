<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\State;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Backend\App\Area\FrontNameResolver;
use Amasty\Checkout\Setup\Operation\ConfigDataRegroup;

/**
 * UpgradeData For Database
 *
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Operation\UpgradeDataTo203
     */
    private $upgradeDataTo203;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ConfigDataRegroup
     */
    private $configRegroup;

    public function __construct(
        Operation\UpgradeDataTo203 $upgradeDataTo203,
        State $appState,
        ConfigDataRegroup $configRegroup
    ) {
        $this->upgradeDataTo203 = $upgradeDataTo203;
        $this->appState = $appState;
        $this->configRegroup = $configRegroup;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->appState->emulateAreaCode(
            FrontNameResolver::AREA_CODE,
            [$this, 'upgradeDataWithEmulationAreaCode'],
            [$setup, $context]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgradeDataWithEmulationAreaCode(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->upgradeDataTo203->execute();
        }

        if (version_compare($context->getVersion(), '2.10.0', '<')) {
            $this->configRegroup->execute();
        }

        $setup->endSetup();
    }
}
