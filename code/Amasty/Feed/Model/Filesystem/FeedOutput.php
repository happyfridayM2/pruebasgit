<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Filesystem;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Model\Config;
use Amasty\Feed\Model\Config\Source\StorageFolder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class FeedOutput
 */
class FeedOutput
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var Compressor
     */
    private $compressor;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var WriteInterface
     */
    private $dir;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        Compressor $compressor,
        Config $config
    ) {
        $this->filesystem = $filesystem;
        $this->compressor = $compressor;
        $this->config = $config;
        if ($this->config->getStorageFolder() == StorageFolder::VAR_FOLDER) {
            $this->dir = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        } else {
            $this->dir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
    }

    public function get(\Amasty\Feed\Model\Feed $feed)
    {
        $directoryPath = trim($this->config->getFilePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $outputFilename = $filename = $directoryPath . $feed->getFilename();

        if ($feed->getCompress()) {
            $outputFilename .= '.' . $feed->getCompress();
            if ($this->dir->isExist($filename)) {
                if ($this->dir->isExist($outputFilename)) {
                    $this->dir->delete($outputFilename);
                }

                try {
                    $this->compressor->pack(
                        $feed->getCompress(),
                        $this->dir->getAbsolutePath($filename),
                        $this->dir->getAbsolutePath($outputFilename)
                    );
                    $this->dir->delete($filename);
                } catch (LocalizedException $exception) {
                    $outputFilename = $filename;
                }
            }
        }

        return [
            'filename' => $outputFilename,
            'absolute_path' => $this->dir->getAbsolutePath($outputFilename),
            'content' => $this->dir->readFile($outputFilename),
            'mtime' => $this->dir->stat($outputFilename)['mtime']
        ];
    }

    public function delete(FeedInterface $feed)
    {
        $directoryPath = trim($this->config->getFilePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $filename = $directoryPath . $feed->getFilename();
        if ($this->dir->isFile($filename)) {
            $this->dir->delete($filename);
        }
    }
}
