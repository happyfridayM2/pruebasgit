<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin\Deploying;

use Amasty\PageSpeedOptimizer\Api\Data\BundleFileInterface;
use Amasty\PageSpeedOptimizer\Model\OptionSource\BundlingType;

/**
 * Class BundlingMagento21 is for excluding files from bundling
 * previously saved in \Amasty\PageSpeedOptimizer\Controller\Bundle\Modules
 * for Magento < 2.2 version
 */
class BundlingMagento21
{
    /**
     * @var array
     */
    public $files;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider,
        \Magento\Framework\Registry $registry,
        \Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\CollectionFactory $collectionFactory
    ) {
        $this->configProvider = $configProvider;
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
        $this->files = null;
    }

    public function aroundAddAsset($subject, \Closure $proceed, \Magento\Framework\View\Asset\LocalInterface $asset)
    {
        if (!$this->configProvider->isEnabled()
            || $this->configProvider->getBundlingType() !== BundlingType::SUPER_BUNDLING
        ) {
            return $proceed($asset);
        }

        if ($this->files === null) {
            $this->files = $this->getBundlingFiles();
        }

        if ($this->files) {
            $context = $asset->getContext();
            if (empty($this->files[$context->getAreaCode()][$context->getThemePath()][$context->getLocale()])) {
                return $proceed($asset);
            } else {
                if (in_array(
                    (($asset->getModule() == '') ? '' : $asset->getModule() . '/') . $asset->getFilePath(),
                    $this->files[$context->getAreaCode()][$context->getThemePath()][$context->getLocale()]
                )) {
                    return $proceed($asset);
                }
            }
        } else {
            return $proceed($asset);
        }
    }

    public function getBundlingFiles()
    {
        $result = [];
        $collection = $this->collectionFactory->create();
        foreach ($collection->getData() as $item) {
            $result[$item[BundleFileInterface::AREA]][$item[BundleFileInterface::THEME]]
                [$item[BundleFileInterface::LOCALE]][] = $item[BundleFileInterface::FILENAME];
        }

        return $result;
    }
}
