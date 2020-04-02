<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin;

use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Config;

class ExcludePageFromMergeBundle
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        ConfigProvider $configProvider,
        State $appState,
        RequestInterface $request,
        UrlInterface $url
    ) {
        $this->configProvider = $configProvider;
        $this->appState = $appState;
        $this->url = $url;
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function isSkipJs()
    {
        if ($this->request->getParam('amoptimizer_not_merge')) {
            return true;
        }

        try {
            if ($this->appState->getAreaCode() === Area::AREA_ADMINHTML) {

                return !$this->configProvider->adminhtmlJsMergeBundle();
            } elseif ($this->appState->getAreaCode() === Area::AREA_FRONTEND) {
                $exclude = false;
                foreach ($this->configProvider->getExcludeUrlsFromMergeBundleJs() as $url) {
                    if (strpos($this->url->getCurrentUrl(), $url) !== false) {
                        $exclude = true;
                        break;
                    }
                }

                return $exclude;
            }
        } catch (LocalizedException $e) {
            return false;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isSkipCss()
    {
        if ($this->request->getParam('amoptimizer_not_merge')) {
            return true;
        }

        try {
            if ($this->appState->getAreaCode() === Area::AREA_ADMINHTML) {

                return !$this->configProvider->adminhtmlMergeCss();
            } elseif ($this->appState->getAreaCode() === Area::AREA_FRONTEND) {
                $exclude = false;
                foreach ($this->configProvider->getExcludeUrlsFromMergeCss() as $url) {
                    if (strpos($this->url->getCurrentUrl(), $url) !== false) {
                        $exclude = true;
                        break;
                    }
                }

                return $exclude;
            }
        } catch (LocalizedException $e) {
            return false;
        }

        return false;
    }

    /**
     * @param Config   $subject
     * @param \Closure $proceed
     *
     * @return bool
     */
    public function aroundIsMergeJsFiles(Config $subject, \Closure $proceed)
    {
        if ($this->configProvider->isEnabled() && $this->isSkipJs()) {
            return false;
        }

        return $proceed();
    }

    /**
     * @param Config   $subject
     * @param \Closure $proceed
     *
     * @return bool
     */
    public function aroundIsBundlingJsFiles(Config $subject, \Closure $proceed)
    {
        if ($this->configProvider->isEnabled() && $this->isSkipJs()) {
            return false;
        }

        return $proceed();
    }

    /**
     * @param Config   $subject
     * @param \Closure $proceed
     *
     * @return bool
     */
    public function aroundIsMergeCssFiles(Config $subject, \Closure $proceed)
    {
        if ($this->configProvider->isEnabled() && $this->isSkipCss()) {
            return false;
        }

        return $proceed();
    }
}
