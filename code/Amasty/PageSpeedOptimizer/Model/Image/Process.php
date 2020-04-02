<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Image;

use Amasty\PageSpeedOptimizer\Api\Data\QueueInterface;
use Amasty\PageSpeedOptimizer\Model\OptionSource\GifOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\JpegOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\PngOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\ResizeAlgorithm;
use Amasty\PageSpeedOptimizer\Model\OptionSource\Resolutions;
use Amasty\PageSpeedOptimizer\Model\OptionSource\WebpOptimization;
use Magento\Framework\App\Filesystem\DirectoryList;

class Process
{
    const DUMP_DIRECTORY = 'amasty' . DIRECTORY_SEPARATOR . 'amoptimizer_dump' . DIRECTORY_SEPARATOR;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var \Magento\Framework\Image\Adapter\Gd2
     */
    private $gd2;

    /**
     * @var \Magento\Framework\Shell
     */
    private $shell;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\Adapter\Gd2 $gd2,
        \Magento\Framework\Shell $shell,
        \Amasty\PageSpeedOptimizer\Model\Image\ToolChecker $toolChecker
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->gd2 = $gd2;
        $this->shell = $shell;
    }

    /**
     * @param QueueInterface $queue
     *
     * @return array
     */
    public function execute($queue)
    {
        $output = [];

        if (!$this->mediaDirectory->isExist($queue->getFilename())) {
            return $output;
        }

        if ($this->mediaDirectory->isExist(self::DUMP_DIRECTORY . $queue->getFilename())) {
            $this->mediaDirectory->copyFile(self::DUMP_DIRECTORY . $queue->getFilename(), $queue->getFilename());
        }

        switch ($queue->getExtension()) {
            case 'jpg':
            case 'jpeg':
                $this->processJpeg($queue);
                break;
            case 'png':
                $this->processPng($queue);
                break;
            case 'gif':
                $this->processGif($queue);
                break;
        }

        return $output;
    }

    public function processJpeg(QueueInterface $queue)
    {
        $imagePath = $this->mediaDirectory->getAbsolutePath($queue->getFilename());

        if ($queue->getResolutions()) {
            $command = false;
            if ($queue->getTool()) {
                $command = JpegOptimization::TOOLS[$queue->getTool()]['command'];
            }
            $this->processResolutions(
                $queue,
                $command
            );
        }
        if ($queue->isUseWebP()) {
            $this->createWebp($imagePath, $this->getWebpFileName($imagePath, $queue));
        }
        if ($queue->isDumpOriginal()) {
            $this->dumpOriginalImage($queue->getFilename());
        }

        if ($queue->getTool()) {
            try {
                $this->shell->execute(JpegOptimization::TOOLS[$queue->getTool()]['command'], [$imagePath]);
            } catch (\Exception $e) {
                null;
            }
        }
    }

    public function processPng(QueueInterface $queue)
    {
        $imagePath = $this->mediaDirectory->getAbsolutePath($queue->getFilename());

        if ($queue->getResolutions()) {
            $command = false;
            if ($queue->getTool()) {
                $command = PngOptimization::TOOLS[$queue->getTool()]['command'];
            }
            $this->processResolutions(
                $queue,
                $command
            );
        }
        if ($queue->isUseWebP()) {
            $this->createWebp($imagePath, $this->getWebpFileName($imagePath, $queue));
        }
        if ($queue->isDumpOriginal()) {
            $this->dumpOriginalImage($queue->getFilename());
        }

        if ($queue->getTool()) {
            try {
                $this->shell->execute(PngOptimization::TOOLS[$queue->getTool()]['command'], [$imagePath]);
            } catch (\Exception $e) {
                null;
            }
        }
    }

    public function processGif(QueueInterface $queue)
    {
        $imagePath = $this->mediaDirectory->getAbsolutePath($queue->getFilename());

        if ($queue->isUseWebP()) {
            $this->createWebp($imagePath, $this->getWebpFileName($imagePath, $queue));
        }

        if ($queue->isDumpOriginal()) {
            $this->dumpOriginalImage($queue->getFilename());
        }

        if ($queue->getTool()) {
            try {
                $this->shell->execute(GifOptimization::TOOLS[$queue->getTool()]['command'], [$imagePath]);
            } catch (\Exception $e) {
                null;
            }
        }
    }

    public function processResolutions(QueueInterface $queue, $command)
    {
        $imagePath = $this->mediaDirectory->getAbsolutePath($queue->getFilename());
        $resolutions = $queue->getResolutions();

        try {
            $this->gd2->open($imagePath);
        } catch (\Exception $e) {
            return;
        }

        $width = $this->gd2->getOriginalWidth();
        $height = $this->gd2->getOriginalHeight();
        if ($width == 0 || $height == 0) {
            return;
        }
        $this->gd2->keepAspectRatio(true);

        foreach (Resolutions::RESOLUTIONS as $resolutionKey => $resolutionData) {
            if (in_array($resolutionKey, $resolutions) && $width > $resolutionData['width']) {
                switch ($queue->getResizeAlgorithm()) {
                    case ResizeAlgorithm::RESIZE:
                        try {
                            $this->gd2->resize($resolutionData['width']);
                        } catch (\Exception $e) {
                            continue 2;
                        }
                        break;
                    case ResizeAlgorithm::CROP:
                        try {
                            $this->gd2->crop(0, 0, $width - $resolutionData['width'], 0);
                        } catch (\Exception $e) {
                            continue 2;
                        }
                        break;
                }

                $newName = str_replace(
                    $queue->getFilename(),
                    $resolutionData['dir'] . $queue->getFilename(),
                    $imagePath
                );
                if (!$this->mediaDirectory->isExist($this->dirname($newName))) {
                    $this->mediaDirectory->create($this->dirname($newName));
                }
                $this->gd2->save($newName);

                if ($queue->isUseWebP()) {
                    $webP = str_replace('.' .  $queue->getExtension(), '.webp', $newName);
                    $this->createWebp($newName, $webP);
                }

                if ($command) {
                    try {
                        $this->shell->execute($command, [$newName]);
                    } catch (\Exception $e) {
                        null;
                    }
                }

                $this->gd2->open($imagePath);
            }
        }
    }

    public function getWebpFileName($imagePath, \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface $queue)
    {
        $webPPath = str_replace(
            $queue->getFilename(),
            Resolutions::WEBP_DIR . $queue->getFilename(),
            $imagePath
        );
        if (!$this->mediaDirectory->isExist($this->dirname($webPPath))) {
            $this->mediaDirectory->create($this->dirname($webPPath));
        }

        return str_replace('.' . $queue->getExtension(), '.webp', $webPPath);
    }

    public function createWebp($imagePath, $webpPath)
    {
        try {
            $this->shell->execute(WebpOptimization::WEBP['command'], [$imagePath, $webpPath]);
        } catch (\Exception $e) {
            null;
        }
    }

    public function dumpOriginalImage($imagePath)
    {
        $dumpImagePath = self::DUMP_DIRECTORY . $imagePath;

        if (!$this->mediaDirectory->isExist($dumpImagePath)) {
            $this->mediaDirectory->copyFile($imagePath, $dumpImagePath);
        }
    }

    public function removeDumpImage($imagePath)
    {
        $dumpImagePath = self::DUMP_DIRECTORY . $imagePath;

        if ($this->mediaDirectory->isExist($dumpImagePath)) {
            $this->mediaDirectory->delete($dumpImagePath);
        }
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function dirname($file)
    {
        //phpcs:ignore
        return dirname($file);
    }
}
