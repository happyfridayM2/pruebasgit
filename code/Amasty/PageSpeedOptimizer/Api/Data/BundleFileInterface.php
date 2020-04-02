<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Api\Data;

interface BundleFileInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const BUNDLE_FILE_ID = 'filename_id';
    const FILENAME = 'filename';
    const AREA = 'area';
    const THEME = 'theme';
    const LOCALE = 'locale';
    /**#@-*/

    /**
     * @return int
     */
    public function getFilenameId();

    /**
     * @param int $filenameId
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\BundleFileInterface
     */
    public function setFilenameId($filenameId);

    /**
     * @return string
     */
    public function getFilename();

    /**
     * @param string $filename
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\BundleFileInterface
     */
    public function setFilename($filename);

    /**
     * @return string
     */
    public function getArea();

    /**
     * @param string $area
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\BundleFileInterface
     */
    public function setArea($area);

    /**
     * @return string
     */
    public function getTheme();

    /**
     * @param string $theme
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\BundleFileInterface
     */
    public function setTheme($theme);

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @param string $locale
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\BundleFileInterface
     */
    public function setLocale($locale);
}
