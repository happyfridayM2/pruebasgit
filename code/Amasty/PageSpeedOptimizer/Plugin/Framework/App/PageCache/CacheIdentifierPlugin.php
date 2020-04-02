<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin\Framework\App\PageCache;

use Amasty\PageSpeedOptimizer\Model\Output\DeviceDetect;
use Amasty\PageSpeedOptimizer\Model\ConfigProvider;

/**
 * Plugin change cache key to show correct pages for different devices
 */
class CacheIdentifierPlugin
{
    /**
     * @var DeviceDetect
     */
    private $deviceDetect;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        DeviceDetect $deviceDetect,
        ConfigProvider $configProvider
    ) {
        $this->deviceDetect = $deviceDetect;
        $this->configProvider = $configProvider;
    }

    /**
     * @param \Magento\Framework\App\PageCache\Identifier $identifier
     * @param string $result
     * @return string
     */
    public function afterGetValue(\Magento\Framework\App\PageCache\Identifier $identifier, $result)
    {
        if (!$this->configProvider->isReplaceImagesUsingUserAgent()) {
            return $result;
        }

        return $result . 'amasty_' . $this->deviceDetect->getDeviceType() . (int)$this->deviceDetect->isUseWebP();
    }
}
