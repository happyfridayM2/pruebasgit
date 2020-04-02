<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\HeaderProvider;

class IsSetXFrameOptions
{
    /**
     * @var bool
     */
    private $isSetHeader = false;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param $isSetHeader
     *
     * @return $this
     */
    public function setIsSetHeader($isSetHeader)
    {
        $this->isSetHeader = (bool)$isSetHeader;

        return $this;
    }

    public function isSetHeader()
    {
        return $this->isSetHeader;
    }

    /**
     * @param string $baseUrl
     *
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
}
