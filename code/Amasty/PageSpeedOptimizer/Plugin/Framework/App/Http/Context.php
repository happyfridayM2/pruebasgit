<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin\Framework\App\Http;

use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Amasty\PageSpeedOptimizer\Model\Output\DeviceDetect;
use Magento\Framework\App\Http\Context as HttpContext;

class Context
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var DeviceDetect
     */
    private $deviceDetect;

    public function __construct(
        DeviceDetect $deviceDetect,
        ConfigProvider $configProvider
    ) {
        $this->deviceDetect = $deviceDetect;
        $this->configProvider = $configProvider;
    }

    public function beforeGetVaryString(HttpContext $subject)
    {
        if (!$this->configProvider->isReplaceImagesUsingUserAgent()) {
            return;
        }

        $subject->setValue(
            'amasty_device_type',
            $this->deviceDetect->getDeviceType(),
            DeviceDetect::DESKTOP
        );
        $subject->setValue(
            'amasty_is_use_webp',
            (int)$this->deviceDetect->isUseWebP(),
            0
        );
    }
}
