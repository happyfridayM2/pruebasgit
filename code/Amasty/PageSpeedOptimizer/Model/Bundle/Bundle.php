<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Bundle;

use Amasty\PageSpeedOptimizer\Api\Data\BundleFileInterface;
use Magento\Framework\Model\AbstractModel;

class Bundle extends AbstractModel implements BundleFileInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\Bundle::class);
        $this->setIdFieldName(BundleFileInterface::BUNDLE_FILE_ID);
    }

    /**
     * @inheritdoc
     */
    public function getFilenameId()
    {
        return (int)$this->_getData(BundleFileInterface::BUNDLE_FILE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setFilenameId($filenameId)
    {
        return $this->setData(BundleFileInterface::BUNDLE_FILE_ID, (int)$filenameId);
    }

    /**
     * @inheritdoc
     */
    public function getFilename()
    {
        return $this->_getData(BundleFileInterface::FILENAME);
    }

    /**
     * @inheritdoc
     */
    public function setFilename($filename)
    {
        return $this->setData(BundleFileInterface::FILENAME, $filename);
    }

    /**
     * @inheritdoc
     */
    public function getArea()
    {
        return $this->_getData(BundleFileInterface::AREA);
    }

    /**
     * @inheritdoc
     */
    public function setArea($area)
    {
        return $this->setData(BundleFileInterface::AREA, $area);
    }

    /**
     * @inheritdoc
     */
    public function getTheme()
    {
        return $this->_getData(BundleFileInterface::THEME);
    }

    /**
     * @param string $theme
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\BundleFileInterface
     */
    public function setTheme($theme)
    {
        return $this->setData(BundleFileInterface::THEME, $theme);
    }

    /**
     * @inheritdoc
     */
    public function getLocale()
    {
        return $this->_getData(BundleFileInterface::LOCALE);
    }

    /**
     * @param string $locale
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\BundleFileInterface
     */
    public function setLocale($locale)
    {
        return $this->setData(BundleFileInterface::LOCALE, $locale);
    }
}
