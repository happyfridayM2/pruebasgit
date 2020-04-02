<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetaData;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var Operation\UpgradeTo101
     */
    private $upgradeTo101;

    /**
     * @var Operation\UpgradeTo114
     */
    private $upgradeTo114;

    /**
     * @var Operation\UpgradeTo135
     */
    private $upgradeTo135;

    /**
     * @var Operation\UpgradeTo180
     */
    private $upgradeTo180;

    /**
     * @var Operation\UpgradeDataTo191
     */
    private $upgradeDataTo191;

    /**
     * @var Operation\UpgradeDataTo210
     */
    private $upgradeDataTo210;

    /**
     * @var Operation\UpgradeDataTo220
     */
    private $upgradeDataTo220;

    /**
     * @var Operation\UpgradeDataTo227
     */
    private $upgradeDataTo227;

    /**
     * @var \Amasty\Feed\Model\Import
     */
    private $import;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetaData,
        \Magento\Framework\App\State $appState,
        \Amasty\Feed\Model\Import $import,
        Operation\UpgradeTo101 $upgradeTo101,
        Operation\UpgradeTo114 $upgradeTo114,
        Operation\UpgradeTo135 $upgradeTo135,
        Operation\UpgradeTo180 $upgradeTo180,
        Operation\UpgradeDataTo191 $upgradeDataTo191,
        Operation\UpgradeDataTo210 $upgradeDataTo210,
        Operation\UpgradeDataTo220 $upgradeDataTo220,
        Operation\UpgradeDataTo227 $upgradeDataTo227
    ) {
        $this->productMetaData = $productMetaData;
        $this->appState = $appState;
        $this->upgradeTo101 = $upgradeTo101;
        $this->upgradeTo114 = $upgradeTo114;
        $this->upgradeTo135 = $upgradeTo135;
        $this->upgradeTo180 = $upgradeTo180;
        $this->upgradeDataTo191 = $upgradeDataTo191;
        $this->upgradeDataTo210 = $upgradeDataTo210;
        $this->upgradeDataTo220 = $upgradeDataTo220;
        $this->upgradeDataTo227 = $upgradeDataTo227;
        $this->import = $import;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->appState->emulateAreaCode(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
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
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->upgradeTo101->execute();
        }

        if (version_compare($context->getVersion(), '2.3.1', '<')) {
            $this->upgradeTo114->execute();
        }

        if (version_compare($context->getVersion(), '1.3.5', '<')
            && $this->productMetaData->getVersion() >= "2.2.0"
        ) {
            $this->upgradeTo135->execute();
        }

        if (version_compare($context->getVersion(), '1.8.0', '<')) {
            $this->upgradeTo180->execute();
        }

        if (version_compare($context->getVersion(), '1.9.1', '<')) {
            $this->upgradeDataTo191->execute();
        }

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->upgradeDataTo210->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            $this->upgradeDataTo220->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.2.7', '<')) {
            $this->upgradeDataTo227->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.3.0', '<')) {
            $this->import->update('google');
        }

        if (version_compare($context->getVersion(), '2.3.6', '<')) {
            $this->import->update(['amazonProduct', 'amazonInventory', 'amazonPrice', 'amazonImage']);
        }

        $setup->endSetup();
    }
}
