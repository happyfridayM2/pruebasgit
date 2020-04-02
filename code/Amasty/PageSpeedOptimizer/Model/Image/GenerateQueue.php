<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Image;

use Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface;
use Amasty\PageSpeedOptimizer\Exceptions\DisabledExecFunction;
use Amasty\PageSpeedOptimizer\Exceptions\ToolNotInstalled;
use Amasty\PageSpeedOptimizer\Model\OptionSource\GifOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\JpegOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\PngOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\Resolutions;
use Amasty\PageSpeedOptimizer\Model\OptionSource\WebpOptimization;
use Magento\Framework\App\Filesystem\DirectoryList;

class GenerateQueue
{
    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Queue\QueueRepository
     */
    private $queueRepository;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $mediaDirectory;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Queue\QueueFactory
     */
    private $queueFactory;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ToolChecker
     */
    private $toolChecker;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $file;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Api\QueueRepositoryInterface $queueRepository,
        \Amasty\PageSpeedOptimizer\Model\Queue\QueueFactory $queueFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider,
        \Amasty\PageSpeedOptimizer\Model\Image\ResourceModel\CollectionFactory $collectionFactory,
        \Amasty\PageSpeedOptimizer\Model\Image\ToolChecker $toolChecker,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->queueRepository = $queueRepository;
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->configProvider = $configProvider;
        $this->queueFactory = $queueFactory;
        $this->collectionFactory = $collectionFactory;
        $this->toolChecker = $toolChecker;
        $this->file = $file;
    }

    /**
     * @param null|int $imageSettingId
     *
     * @return int
     */
    public function generateQueue($imageSettingId = null)
    {
        $this->queueRepository->clearQueue();
        $this->processFiles($imageSettingId);

        return $this->queueRepository->getQueueSize();
    }

    /**
     * @param null|int $imageSettingId
     *
     * @return void
     */
    public function processFiles($imageSettingId)
    {
        $imageFolders = $this->prepareFolders($imageSettingId);
        foreach ($imageFolders as $imageDirectory => $imageSetting) {
            $this->checkTools($imageSetting);
            $files = $this->mediaDirectory->readRecursively($imageDirectory);
            foreach ($files as $file) {
                $skip = false;
                foreach (Resolutions::RESOLUTIONS as $resolution) {
                    if (strpos($file, $resolution['dir']) !== false) {
                        $skip = true;
                    }
                }
                if (!$skip && strpos($file, Process::DUMP_DIRECTORY) === false
                    && $this->mediaDirectory->isFile($file)
                ) {
                    $pathInfo = $this->file->getPathInfo($file);
                    $ext = strtolower($pathInfo['extension']);
                    switch ($ext) {
                        case 'jpg':
                        case 'jpeg':
                            $skip = !$imageSetting->getJpegTool() && !$imageSetting->isCreateWebp();
                            $tool = $imageSetting->getJpegTool();
                            break;
                        case 'png':
                            $skip = !$imageSetting->getPngTool() && !$imageSetting->isCreateWebp();
                            $tool = $imageSetting->getPngTool();
                            break;
                        case 'gif':
                            $skip = !$imageSetting->getGifTool() && !$imageSetting->isCreateWebp();
                            $tool = $imageSetting->getGifTool();
                            break;
                        default:
                            $skip = true;
                    }
                    $dir = $pathInfo['dirname'];
                    if ($dir !== $imageDirectory && isset($imageFolders[$dir])) {
                        $skip = true;
                    }
                    if ($skip) {
                        continue;
                    }
                    $resolutions = [];
                    if ($imageSetting->isCreateMobileResolution()) {
                        $resolutions[] = Resolutions::MOBILE;
                    }
                    if ($imageSetting->isCreateTabletResolution()) {
                        $resolutions[] = Resolutions::TABLET;
                    }
                    /** @var \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface $queue */
                    $queue = $this->queueFactory->create();
                    $queue->setFilename($file)
                        ->setExtension($ext)
                        ->setResolutions($resolutions)
                        ->setTool($tool)
                        ->setIsUseWebP($imageSetting->isCreateWebp())
                        ->setIsDumpOriginal($imageSetting->isDumpOriginal())
                        ->setResizeAlgorithm($imageSetting->getResizeAlgorithm());

                    $this->queueRepository->addToQueue($queue);
                }
            }
        }
    }

    /**
     * @param int|null $imageSettingId
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface[];
     */
    public function prepareFolders($imageSettingId)
    {
        $imageSettingCollecion = $this->collectionFactory->create();
        $imageSettingCollecion->addFieldToFilter(ImageSettingInterface::IS_ENABLED, 1);
        if ($imageSettingId) {
            $imageSettingCollecion->addFieldToFilter(ImageSettingInterface::IMAGE_SETTING_ID, (int)$imageSettingId);
        }
        $folders = [];
        /** @var \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface $item */
        foreach ($imageSettingCollecion->getItems() as $item) {
            foreach ($item->getFolders() as $folder) {
                $folders[$folder] = $item;
            }
        }

        return $folders;
    }

    public function checkTools(\Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface &$imageSetting)
    {
        try {
            if ($imageSetting->getJpegTool()) {
                try {
                    $this->toolChecker->check(JpegOptimization::TOOLS[$imageSetting->getJpegTool()]);
                } catch (ToolNotInstalled $e) {
                    $imageSetting->setJpegTool(0);
                }
            }

            if ($imageSetting->getPngTool()) {
                try {
                    $this->toolChecker->check(PngOptimization::TOOLS[$imageSetting->getPngTool()]);
                } catch (ToolNotInstalled $e) {
                    $imageSetting->setPngTool(0);
                }
            }

            if ($imageSetting->getGifTool()) {
                try {
                    $this->toolChecker->check(GifOptimization::TOOLS[$imageSetting->getGifTool()]);
                } catch (ToolNotInstalled $e) {
                    $imageSetting->setGifTool(0);
                }
            }

            if ($imageSetting->isCreateWebp()) {
                try {
                    $this->toolChecker->check(WebpOptimization::WEBP);
                } catch (ToolNotInstalled $e) {
                    $imageSetting->setIsCreateWebp(false);
                }
            }
        } catch (DisabledExecFunction $e) {
            $imageSetting->setJpegTool(0)
                ->setPngTool(0)
                ->setGifTool(0)
                ->setIsCreateWebp(0);
        }
    }
}
