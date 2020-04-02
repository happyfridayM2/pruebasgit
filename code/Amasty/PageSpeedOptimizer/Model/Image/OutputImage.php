<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Image;

use Amasty\PageSpeedOptimizer\Model\OptionSource\Resolutions;
use Magento\Framework\App\Filesystem\DirectoryList;

class OutputImage extends \Magento\Framework\DataObject
{
    const PATH = 'path';
    const PROCESS = 'process';
    const MOBILE_PATH = 'mobile_path';
    const TABLET_PATH = 'tablet_path';
    const WEBP_PATH = 'webp_path';
    const WEBP_MOBILE_PATH = 'webp_mobile_path';
    const WEBP_TABLET_PATH = 'webp_tablet_path';

    /**
     * @var string
     */
    private $storeMediaUrl;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $file;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\Io\File $file,
        array $data = []
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryread(DirectoryList::MEDIA);
        $this->storeMediaUrl = $storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        parent::__construct($data);
        $this->file = $file;
    }

    public function setPath($path)
    {
        if (strpos($path, $this->storeMediaUrl) === false) {
            $this->setProcess(false);

            return $this;
        }

        $this->setProcess(true);
        $this->setData(self::PATH, $path);
        $this->setImageData();

        return $this;
    }

    public function getPath()
    {
        return $this->_getData(self::PATH);
    }

    public function setProcess($process)
    {
        return $this->setData(self::PROCESS, (bool)$process);
    }

    public function process()
    {
        return (bool)$this->_getData(self::PROCESS);
    }

    public function setWebpPath($webpPath)
    {
        return $this->setData(self::WEBP_PATH, $webpPath);
    }

    public function getWebpPath()
    {
        return $this->_getData(self::WEBP_PATH);
    }

    public function setMobilePath($mobilePath)
    {
        return $this->setData(self::MOBILE_PATH, $mobilePath);
    }

    public function getMobilePath()
    {
        return $this->_getData(self::MOBILE_PATH);
    }

    public function setTabletPath($tabletPath)
    {
        return $this->setData(self::TABLET_PATH, $tabletPath);
    }

    public function getTabletPath()
    {
        return $this->_getData(self::TABLET_PATH);
    }

    public function setWebpMobilePath($webpMobilePath)
    {
        return $this->setData(self::WEBP_MOBILE_PATH, $webpMobilePath);
    }

    public function getWebpMobilePath()
    {
        return $this->_getData(self::WEBP_MOBILE_PATH);
    }

    public function setWebpTabletPath($webpTabletPath)
    {
        return $this->setData(self::WEBP_TABLET_PATH, $webpTabletPath);
    }

    public function getWebpTabletPath()
    {
        return $this->_getData(self::WEBP_TABLET_PATH);
    }

    public function setImageData()
    {
        $pathInfo = $this->file->getPathInfo($this->getPath());
        $baseName = $pathInfo['basename'];
        $extension = $pathInfo['extension'];
        $path = trim(str_replace($this->storeMediaUrl, '', $this->getPath()), '/');

        $webpName = str_replace('.' . $extension, '.webp', $baseName);
        $webpPath = str_replace($baseName, $webpName, Resolutions::WEBP_DIR . $path);
        if ($this->mediaDirectory->isExist($webpPath)) {
            $this->setWebpPath(str_replace($path, $webpPath, $this->getPath()));
        } else {
            $this->setWebpPath(false);
        }

        foreach (Resolutions::RESOLUTIONS as $data) {
            foreach (['webp_' => $webpName, '' => $baseName] as $prefix => $fName) {
                $curPath = $data['dir'] . $path;
                if ($this->mediaDirectory->isExist(str_replace($baseName, $fName, $curPath))) {
                    $this->setData(
                        $prefix . $data['path'],
                        str_replace($path, str_replace($baseName, $fName, $curPath), $this->getPath())
                    );
                } else {
                    $this->setData($prefix . $data['path'], false);
                }
            }
        }
    }

    public function getSourceSet()
    {
        if (!$this->getWebpPath() && !$this->getWebpMobilePath() && !$this->getWebpTabletPath()
            && !$this->getMobilePath() && !$this->getTabletPath() || !$this->process()
        ) {
            return false;
        }
        $result = '';
        foreach (Resolutions::RESOLUTIONS as $data) {
            foreach (['webp_' => 'image/webp', '' => ''] as $prefix => $type) {
                if ($this->_getData($prefix . $data['path'])) {
                    $result .= '<source srcset="' . $this->_getData($prefix . $data['path']) . '"';
                    if (!empty($data['width'])) {
                        $result .= 'media="(max-width: ' . $data['width'] . 'px)'
                            . (!empty($data['min-width']) ? 'and (min-width: ' . $data['min-width'] . 'px)' : '') . '"';
                    }
                    if (!empty($type)) {
                        $result .= 'type="' . $type . '"';
                    }
                    $result .= '>';
                }
            }
        }

        if ($this->getWebpPath()) {
            $result .= '<source srcset="' . $this->getWebpPath() . '" type="image/webp">';
        }

        if (!empty($result)) {
            $result .= '<source srcset="' . $this->getPath() . '">';
        }

        return $result;
    }

    public function getBest($type, $isWebp)
    {
        if ($isWebp) {
            switch ($type) {
                case 'desktop':
                    if ($this->getWebpPath()) {
                        return $this->getWebpPath();
                    }
                    break;
                case 'tablet':
                    if ($this->getWebpTabletPath()) {
                        return $this->getWebpTabletPath();
                    } elseif ($this->getTabletPath()) {
                        return $this->getTabletPath();
                    } elseif ($this->getWebpPath()) {
                        return $this->getWebpPath();
                    }
                    break;
                case 'mobile':
                    if ($this->getWebpMobilePath()) {
                        return $this->getWebpMobilePath();
                    } elseif ($this->getMobilePath()) {
                        return $this->getMobilePath();
                    } elseif ($this->getWebpTabletPath()) {
                        return $this->getWebpTabletPath();
                    } elseif ($this->getTabletPath()) {
                        return $this->getTabletPath();
                    } elseif ($this->getWebpPath()) {
                        return $this->getWebpPath();
                    }
                    break;
            }

            return $this->getPath();
        } else {
            switch ($type) {
                case 'tablet':
                    if ($this->getTabletPath()) {
                        return $this->getTabletPath();
                    }
                    break;
                case 'mobile':
                    if ($this->getMobilePath()) {
                        return $this->getMobilePath();
                    } elseif ($this->getTabletPath()) {
                        return $this->getTabletPath();
                    }
                    break;
            }
        }

        return $this->getPath();
    }
}
