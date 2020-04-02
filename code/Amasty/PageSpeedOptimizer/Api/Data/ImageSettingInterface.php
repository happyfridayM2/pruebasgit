<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Api\Data;

interface ImageSettingInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const IMAGE_SETTING_ID = 'image_setting_id';
    const IS_ENABLED = 'is_enabled';
    const FOLDERS = 'folders';
    const TITLE = 'title';
    const IS_CREATE_MOBILE_RESOLUTION = 'is_create_mobile_resolution';
    const IS_CREATE_TABLET_RESOLUTION = 'is_create_tablet_resolution';
    const RESIZE_ALGORITHM = 'resize_algorithm';
    const IS_CREATE_WEBP = 'is_create_webp';
    const IS_DUMP_ORIGINAL = 'is_create_dump';
    const JPEG_TOOL = 'jpeg_tool';
    const PNG_TOOL = 'png_tool';
    const GIF_TOOL = 'gif_tool';
    /**#@-*/

    /**
     * @param int $imageSettingId
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     */
    public function setImageSettingId($imageSettingId);

    /**
     * @return int
     */
    public function getImageSettingId();

    /**
     * @param bool $isEnabled
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     */
    public function setIsEnabled($isEnabled);

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @param array $folders
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     */
    public function setFolders($folders);

    /**
     * @return array
     */
    public function getFolders();

    /**
     * @param string $title
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     */
    public function setTitle($title);

    /**
     * @return array
     */
    public function getTitle();

    /**
     * @param bool $isCreateMobileResolution
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     */
    public function setIsCreateMobileResolution($isCreateMobileResolution);

    /**
     * @return bool
     */
    public function isCreateMobileResolution();

    /**
     * @param bool $isCreateTabletResolution
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     */
    public function setIsCreateTabletResolution($isCreateTabletResolution);

    /**
     * @return bool
     */
    public function isCreateTabletResolution();

    /**
     * @param int $resizeAlgorithm
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     */
    public function setResizeAlgorithm($resizeAlgorithm);

    /**
     * @return int
     */
    public function getResizeAlgorithm();

    /**
     * @param bool $isCreateWebp
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     */
    public function setIsCreateWebp($isCreateWebp);

    /**
     * @return bool
     */
    public function isCreateWebp();

    /**
     * @param bool $isDumpOriginal
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     */
    public function setIsDumpOriginal($isDumpOriginal);

    /**
     * @return bool
     */
    public function isDumpOriginal();

    /**
     * @param int $jpegTool
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     */
    public function setJpegTool($jpegTool);

    /**
     * @return int
     */
    public function getJpegTool();

    /**
     * @param int $pngTool
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     */
    public function setPngTool($pngTool);

    /**
     * @return int
     */
    public function getPngTool();

    /**
     * @param int $gifTool
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     */
    public function setGifTool($gifTool);

    /**
     * @return int
     */
    public function getGifTool();
}
