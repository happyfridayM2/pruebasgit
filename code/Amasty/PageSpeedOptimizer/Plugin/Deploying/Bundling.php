<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin\Deploying;

use Amasty\PageSpeedOptimizer\Model\OptionSource\BundlingType;

/**
 * Class Bundling is for excluding files from bundling
 * previously saved in \Amasty\PageSpeedOptimizer\Controller\Bundle\Modules
 */
class Bundling
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
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $file;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->configProvider = $configProvider;
        $this->registry = $registry;
        $this->file = $file;
    }

    public function aroundAddFile($subject, \Closure $proceed, $filePath, $sourcePath, $contentType)
    {
        if (!$this->configProvider->isEnabled()
            || $this->configProvider->getBundlingType() !== BundlingType::SUPER_BUNDLING
        ) {
            return $proceed($filePath, $sourcePath, $contentType);
        }

        if ($this->files === null) {
            $this->files = $this->registry->registry('am_bundle_files');
        }
        if ($this->files) {
            if (in_array($filePath, $this->files) || in_array($this->removeMinifiedSign($filePath), $this->files)) {
                return $proceed($filePath, $sourcePath, $contentType);
            }
        } else {
            return $proceed($filePath, $sourcePath, $contentType);
        }
    }

    public function afterFlush($subject)
    {
        $this->files = null;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public function removeMinifiedSign($filename)
    {
        $pathInfo = $this->file->getPathInfo($filename);

        return substr($filename, 0, -strlen($pathInfo['extension']) - 4) . $pathInfo['extension'];
    }
}
