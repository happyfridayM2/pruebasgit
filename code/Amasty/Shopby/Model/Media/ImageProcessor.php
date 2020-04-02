<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Media;

use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\ImageUploader;

/**
 * Class ImageProcessor
 * @package Amasty\Shopby\Model\Media
 */
class ImageProcessor extends ImageUploader
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var AdapterFactory
     */
    protected $imageFactory;

    /**
     * ImageProcessor constructor.
     *
     * @param Filesystem $filesystem
     * @param AdapterFactory $imageFactory
     * @param Database $coreFileStorageDatabase
     * @param UploaderFactory $uploaderFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param string $baseTmpPath
     * @param string $basePath
     * @param array $allowedExtensions
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Filesystem $filesystem,
        AdapterFactory $imageFactory,
        Database $coreFileStorageDatabase,
        UploaderFactory $uploaderFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        $baseTmpPath,
        $basePath,
        array $allowedExtensions = []
    ) {
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        parent::__construct(
            $coreFileStorageDatabase,
            $filesystem,
            $uploaderFactory,
            $storeManager,
            $logger,
            $baseTmpPath,
            $basePath,
            $allowedExtensions
        );
    }

    /**
     * @param string $image
     * @return void
     */
    public function resize($image)
    {
        $baseTmpPath = $this->getBaseTmpPath();
        $baseTmpImagePath = $this->getFilePath($baseTmpPath, $image);

        $absolutePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath($baseTmpImagePath);

        $imageResize = $this->imageFactory->create();
        $imageResize->open($absolutePath);
        $imageResize->constrainOnly(false);
        $imageResize->keepTransparency(true);
        $imageResize->keepFrame(false);
        $imageResize->keepAspectRatio(true);
        $imageResize->save($absolutePath);
    }
}
