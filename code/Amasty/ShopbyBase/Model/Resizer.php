<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\Adapter\AdapterInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\UrlInterface;
use Magento\Framework\Image\AdapterFactory as ImageAdapterFactory;

/**
 * Class Resizer
 */
class Resizer
{
    const IMAGE_RESIZER_DIR = 'images';

    const IMAGE_RESIZER_CACHE_DIR = self::IMAGE_RESIZER_DIR . DIRECTORY_SEPARATOR . DirectoryList::CACHE;

    /**
     * @var string
     */
    private $relativeFilename;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $fileSystem;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $placeholder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var File
     */
    private $fileIo;

    public function __construct(
        Filesystem $fileSystem,
        Repository $repository,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        File $fileIo,
        ImageAdapterFactory $adapterFactory,
        $placeholder = ''
    ) {
        $this->storeManager = $storeManager;
        $this->fileSystem = $fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->assetRepo = $repository;
        $this->placeholder = $placeholder;
        $this->logger = $logger;
        $this->fileIo = $fileIo;
        $this->imageAdapterFactory = $adapterFactory;
    }

    public function getImageUrl($imageUrl, $width = null, $height = null)
    {
        return $this->resizeAndGetUrl($imageUrl, $width, $height);
    }

    /**
     * @param string $imagePath
     * @return string
     */
    protected function buildUrl($imagePath)
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . $imagePath;
    }

    /**
     * @param string $imageUrl
     * @param int $width
     * @param int $height
     * @return bool|string
     */
    protected function resizeAndGetUrl($imageUrl, $width = null, $height = null)
    {
        $resultUrl = $imageUrl;
        $this->initRelativeFilenameFromUrl($imageUrl);
        if (!$this->relativeFilename) {
            return $resultUrl;
        }
        $this->initSize($width, $height);

        try {
            $resizedUrl = $this->getResizedImageUrl();
            if (!$resizedUrl) {
                if ($this->resizeAndSaveImage()) {
                    $resizedUrl = $this->getResizedImageUrl();
                }
            }
            if ($resizedUrl) {
                $resultUrl = $resizedUrl;
            }
        } catch (\Exception $e) {
            $this->logger->error("Amasty Shopby: Resize Image Error: " . $e->getMessage());
        }
        return $resultUrl;
    }

    /**
     * @param string $imageUrl
     * @return bool|mixed|string
     */
    protected function initRelativeFilenameFromUrl($imageUrl)
    {
        $this->relativeFilename = false;
        $storeUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        if (false !== strpos($imageUrl, $storeUrl)) {
            $relativeFilename = str_replace($storeUrl, '', $imageUrl);
            $this->relativeFilename = $relativeFilename;
        }

        return $this->relativeFilename;
    }

    /**
     * Init resize dimensions
     *
     * @param int $width
     * @param int $height
     * @return void
     */
    protected function initSize($width, $height)
    {
        $this->width = $width;
        $this->height = ($height === null ? $width : $height);
    }

    /**
     * Resize and save new generated image
     *
     * @return bool
     * @throws \Exception
     */
    protected function resizeAndSaveImage()
    {
        if (!$this->width || !$this->height) {
            return false;
        }

        if (!$this->fileSystem->isFile($this->relativeFilename)) {
            return false;
        }

        $imageAdapter = $this->imageAdapterFactory->create();
        $imageAdapter->open($this->getAbsolutePathOriginal());
        $imageAdapter->constrainOnly(false);
        $imageAdapter->keepTransparency(true);
        $imageAdapter->keepFrame(true);
        $imageAdapter->keepAspectRatio(true);
        $imageAdapter->backgroundColor([255, 255, 255]);
        $imageAdapter->resize($this->prepareImageWidth($imageAdapter), $this->prepareImageHeight($imageAdapter));
        $imageAdapter->save($this->getAbsolutePathResized());

        return true;
    }

    /**
     * @param AdapterInterface $imageAdapter
     * @return int
     */
    protected function prepareImageWidth(AdapterInterface $imageAdapter)
    {
        if (!$this->isPercent($this->width)) {
            return $this->width;
        }
        $this->width = $this->getPercentOf($imageAdapter->getOriginalWidth(), $this->width);

        return $this->width;
    }

    /**
     * @param AdapterInterface $imageAdapter
     * @return int
     */
    protected function prepareImageHeight(AdapterInterface $imageAdapter)
    {
        if (!$this->isPercent($this->height)) {
            return $this->height;
        }
        $this->height = $this->getPercentOf($imageAdapter->getOriginalHeight(), $this->height);

        return $this->height;
    }

    /**
     * Get url of resized image
     *
     * @return bool|string
     */
    protected function getResizedImageUrl()
    {
        $relativePath = $this->getRelativePathResizedImage();
        if ($this->fileSystem->isFile($relativePath)) {
            return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $relativePath;
        }

        return false;
    }

    /**
     * Get absolute path from resized image
     *
     * @return string
     */
    protected function getAbsolutePathResized()
    {
        return $this->fileSystem->getAbsolutePath($this->getRelativePathResizedImage());
    }

    /**
     * Get absolute path from original image
     *
     * @return string
     */
    protected function getAbsolutePathOriginal()
    {
        return $this->fileSystem->getAbsolutePath($this->relativeFilename);
    }

    /**
     * Get relative path where the resized image is saved
     *
     * In order to have unique paths, we use the original image path plus the ResizeSubFolderName.
     *
     * @return string
     */
    protected function getRelativePathResizedImage()
    {
        $pathInfo = $this->getPathInfo();
        if (!isset($pathInfo['basename']) || !isset($pathInfo['dirname'])) {
            return false;
        }

        $relativePathParts = [
            self::IMAGE_RESIZER_CACHE_DIR,
            $pathInfo['dirname'],
            $this->getResizeSubFolderName(),
            $pathInfo['basename'],
        ];
        return implode(DIRECTORY_SEPARATOR, $relativePathParts);
    }

    /**
     * @return mixed
     */
    protected function getPathInfo()
    {
        return $this->fileIo->getPathInfo($this->relativeFilename);
    }

    /**
     * Get sub folder name where the resized image will be saved
     *
     * @return string
     */
    protected function getResizeSubFolderName()
    {
        $subPath = 'resized/' . $this->width . "x" . $this->height;
        return $subPath;
    }

    /**
     * @param $needle
     * @return bool
     */
    public function isPercent($needle)
    {
        return !!preg_match('/\d+\%/', $needle);
    }

    /**
     * @param $value
     * @param $percent
     * @return int
     */
    public function getPercentOf($value, $percent)
    {
        return round($value * (int) $percent / 100);
    }
}
