<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Setup;

use Amasty\Base\Helper\Deploy as DeployHelper;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

/**
 * Class UpgradeData
 * @package Amasty\Shopby\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var DeployHelper
     */
    private $deployHelper;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    private $resourceConfig;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Framework\Module\Status
     */
    private $moduleStatus;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    public function __construct(
        DeployHelper $deployHelper,
        CategorySetupFactory $categorySetupFactory,
        CategoryCollectionFactory $categoryFactory,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resourceConfig,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Module\Status $moduleStatus,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->deployHelper = $deployHelper;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->categoryCollectionFactory = $categoryFactory;
        $this->resourceConfig = $resourceConfig;
        $this->appState = $appState;
        $this->filesystem = $filesystem;
        $this->moduleStatus = $moduleStatus;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     * @return void
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $this->appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_ADMINHTML,
            [$this, 'upgradeCallback'],
            [$setup, $context]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     * @return void
     */
    public function upgradeCallback(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), '1.6.3', '<')) {
            $this->deployPub();
        }

        if (version_compare($context->getVersion(), '2.1.5', '<')) {
            $this->addCategoryThumbnail($setup);
        }

        if (version_compare($context->getVersion(), '2.6.0', '<')) {
            $this->setMobileSubmitFilters();
        }

        if (version_compare($context->getVersion(), '2.9.5', '<')) {
            $this->backupShopbyRoot();
        }

        if (version_compare($context->getVersion(), '2.11.2', '<')) {
            $this->turnOffShopbyLite();
        }
    }

    /**
     * @return void
     */
    private function deployPub()
    {
        $p = strrpos(__DIR__, DIRECTORY_SEPARATOR);
        $modulePath = $p ? substr(__DIR__, 0, $p) : __DIR__;
        $modulePath .= '/pub';
        $this->deployHelper->deployFolder($modulePath);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function addCategoryThumbnail(ModuleDataSetupInterface $setup)
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
        if ($categorySetup->getAttribute('catalog_category', 'thumbnail')) {
            return;
        }

        $categorySetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'thumbnail', [
            'type' => 'varchar',
            'label' => 'Thumbnail',
            'input' => 'image',
            'backend' => \Magento\Catalog\Model\Category\Attribute\Backend\Image::class,
            'required' => false,
            'sort_order' => 5,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'group' => 'General Information',
        ]);

        $idGroup = $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');
        $categorySetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $idGroup,
            'thumbnail',
            45
        );
    }

    /**
     * @return void
     */
    private function setMobileSubmitFilters()
    {
        $connection = $this->resourceConfig->getConnection();

        $select = $connection->select()->from(
            $this->resourceConfig->getTable('core_config_data'),
            ['scope', 'scope_id', 'value']
        )->where('path = \'amshopby/general/submit_filters\'');

        foreach ($connection->fetchAll($select) as $config) {
            $type = $config['value'] == 'by_button_click' ? 1 : 0;

            $connection->insertOnDuplicate(
                $this->resourceConfig->getTable('core_config_data'),
                [
                    'scope_id' => $config['scope_id'],
                    'scope' => $config['scope'],
                    'value' => $type,
                    'path' => 'amshopby/general/submit_filters_on_desktop'
                ]
            );
            $connection->insertOnDuplicate(
                $this->resourceConfig->getTable('core_config_data'),
                [
                    'scope_id' => $config['scope_id'],
                    'scope' => $config['scope'],
                    'value' => $type,
                    'path' => 'amshopby/general/submit_filters_on_mobile'
                ]
            );
        }
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function backupShopbyRoot()
    {
        if ($this->moduleManager->isEnabled('Amasty_ShopbyRoot')) {
            $pathToModule = $this->filesystem->getDirectoryRead('app')->getAbsolutePath()
                . 'code/Amasty/ShopbyRoot';

            try {
                $this->moduleStatus->setIsEnabled(false, ['Amasty_ShopbyRoot']);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please remove "%1" folder manually.', $pathToModule)
                );
            }
        }

        $connection = $this->resourceConfig->getConnection();
        $connection->delete($this->resourceConfig->getTable('setup_module'), 'module = "Amasty_ShopbyRoot"');
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function turnOffShopbyLite()
    {
        if ($this->moduleManager->isEnabled('Amasty_ShopbyLite')) {
            try {
                $this->moduleStatus->setIsEnabled(false, ['Amasty_ShopbyLite']);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please disable Amasty_ShopbyLite module manually.')
                );
            }
        }
    }
}
