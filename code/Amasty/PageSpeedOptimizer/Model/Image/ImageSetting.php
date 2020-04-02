<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Image;

use Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface;
use Magento\Framework\Model\AbstractModel;

class ImageSetting extends AbstractModel implements ImageSettingInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\PageSpeedOptimizer\Model\Image\ResourceModel\ImageSetting::class);
        $this->setIdFieldName(ImageSettingInterface::IMAGE_SETTING_ID);
    }

    /**
     * @inheritDoc
     */
    public function setImageSettingId($imageSettingId)
    {
        return $this->setData(ImageSettingInterface::IMAGE_SETTING_ID, (int)$imageSettingId);
    }

    /**
     * @inheritDoc
     */
    public function getImageSettingId()
    {
        return (int)$this->_getData(ImageSettingInterface::IMAGE_SETTING_ID);
    }

    /**
     * @inheritDoc
     */
    public function setIsEnabled($isEnabled)
    {
        return $this->setData(ImageSettingInterface::IS_ENABLED, (bool)$isEnabled);
    }

    /**
     * @inheritDoc
     */
    public function isEnabled()
    {
        return (bool)$this->_getData(ImageSettingInterface::IMAGE_SETTING_ID);
    }

    /**
     * @inheritDoc
     */
    public function setFolders($folders)
    {
        if (empty($folders) || !is_array($folders)) {
            $folders = [];
        }

        return $this->setData(ImageSettingInterface::FOLDERS, json_encode($folders));
    }

    /**
     * @inheritDoc
     */
    public function getFolders()
    {
        $folders = $this->_getData(ImageSettingInterface::FOLDERS);
        if (empty($folders)) {
            return [];
        }
        if (!is_array($folders)) {
            $folders = json_decode($folders, true);
            if (json_last_error()) {
                return [];
            }
        }

        return $folders;
    }

    /**
     * @inheritDoc
     */
    public function setTitle($title)
    {
        return $this->setData(ImageSettingInterface::TITLE, $title);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->_getData(ImageSettingInterface::TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setIsCreateMobileResolution($isCreateMobileResolution)
    {
        return $this->setData(ImageSettingInterface::IS_CREATE_MOBILE_RESOLUTION, (bool)$isCreateMobileResolution);
    }

    /**
     * @inheritDoc
     */
    public function isCreateMobileResolution()
    {
        return (bool)$this->_getData(ImageSettingInterface::IS_CREATE_MOBILE_RESOLUTION);
    }

    /**
     * @inheritDoc
     */
    public function setIsCreateTabletResolution($isCreateTabletResolution)
    {
        return $this->setData(ImageSettingInterface::IS_CREATE_TABLET_RESOLUTION, (bool)$isCreateTabletResolution);
    }

    /**
     * @inheritDoc
     */
    public function isCreateTabletResolution()
    {
        return (bool)$this->_getData(ImageSettingInterface::IS_CREATE_TABLET_RESOLUTION);
    }

    /**
     * @inheritDoc
     */
    public function setResizeAlgorithm($resizeAlgorithm)
    {
        return $this->setData(ImageSettingInterface::RESIZE_ALGORITHM, (int)$resizeAlgorithm);
    }

    /**
     * @inheritDoc
     */
    public function getResizeAlgorithm()
    {
        return (int)$this->_getData(ImageSettingInterface::RESIZE_ALGORITHM);
    }

    /**
     * @inheritDoc
     */
    public function setIsCreateWebp($isCreateWebp)
    {
        return $this->setData(ImageSettingInterface::IS_CREATE_WEBP, (bool)$isCreateWebp);
    }

    /**
     * @inheritDoc
     */
    public function isCreateWebp()
    {
        return (bool)$this->_getData(ImageSettingInterface::IS_CREATE_WEBP);
    }

    /**
     * @inheritDoc
     */
    public function setIsDumpOriginal($isDumpOriginal)
    {
        return $this->setData(ImageSettingInterface::IS_DUMP_ORIGINAL, (bool)$isDumpOriginal);
    }

    /**
     * @inheritDoc
     */
    public function isDumpOriginal()
    {
        return (bool)$this->_getData(ImageSettingInterface::IS_DUMP_ORIGINAL);
    }

    /**
     * @inheritDoc
     */
    public function setJpegTool($jpegTool)
    {
        return $this->setData(ImageSettingInterface::JPEG_TOOL, (int)$jpegTool);
    }

    /**
     * @inheritDoc
     */
    public function getJpegTool()
    {
        return (int)$this->_getData(ImageSettingInterface::JPEG_TOOL);
    }

    /**
     * @inheritDoc
     */
    public function setPngTool($pngTool)
    {
        return $this->setData(ImageSettingInterface::PNG_TOOL, (int)$pngTool);
    }

    /**
     * @inheritDoc
     */
    public function getPngTool()
    {
        return (int)$this->_getData(ImageSettingInterface::PNG_TOOL);
    }

    /**
     * @inheritDoc
     */
    public function setGifTool($gifTool)
    {
        return $this->setData(ImageSettingInterface::GIF_TOOL, (int)$gifTool);
    }

    /**
     * @inheritDoc
     */
    public function getGifTool()
    {
        return (int)$this->_getData(ImageSettingInterface::GIF_TOOL);
    }
}
