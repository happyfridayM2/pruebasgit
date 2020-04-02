<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Setup;

use Amasty\PageSpeedOptimizer\Api\Data\QueueInterface;
use Amasty\PageSpeedOptimizer\Model\Queue\ResourceModel\Queue;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\CreateQueueTable
     */
    private $createQueueTable;

    /**
     * @var Operation\CreateBundleTable
     */
    private $createBundleTable;

    /**
     * @var Operation\CreateImageSettingTable
     */
    private $createImageSettingTable;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        Operation\CreateQueueTable $createQueueTable,
        Operation\CreateBundleTable $createBundleTable,
        Operation\CreateImageSettingTable $createImageSettingTable,
        Filesystem $filesystem
    ) {
        $this->createQueueTable = $createQueueTable;
        $this->createBundleTable = $createBundleTable;
        $this->createImageSettingTable = $createImageSettingTable;
        $this->filesystem = $filesystem;
    }

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($context->getVersion() && version_compare($context->getVersion(), '1.5.2', '<')) {
            $directory = $this->filesystem->getDirectoryRead(DirectoryList::APP);
            if ($directory->isExist('code/Amasty/PageSpeedOptimizer/Model/Output/MoveFontProcessor.php')) {
                throw new \RuntimeException("\nWARNING: This update requires removing"
                    . " folder app/code/Amasty/PageSpeedOptimizer.\n"
                    . "Remove this folder and unpack new version of "
                    . "package into app/code/Amasty/PageSpeedOptimizer.\n"
                    . "Run `php bin/magento setup:upgrade` again\n");
            }
        }

        $setup->startSetup();

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.0.7', '<')) {
            $this->createQueueTable->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->createBundleTable->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.5.0', '<')) {
            $this->createImageSettingTable->execute($setup);
            $setup->getConnection()->addColumn(
                $setup->getTable(Queue::TABLE_NAME),
                QueueInterface::TOOL,
                [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Tool Column'
                ]
            );
        }

        $setup->endSetup();
    }
}
