<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Component\ComponentRegistrar;

class InstallData implements InstallDataInterface
{
    const DEPLOY_DIR = 'pub';

    /**
     * @var \Amasty\Base\Helper\Deploy
     */
    private $deploy;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    private $componentRegistrar;

    public function __construct(
        \Amasty\Base\Helper\Deploy $deploy,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
    ) {

        $this->deploy = $deploy;
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->deploy->deployFolder(
            $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                'Amasty_PageSpeedOptimizer'
            ) . DIRECTORY_SEPARATOR . self::DEPLOY_DIR
        );
    }
}
