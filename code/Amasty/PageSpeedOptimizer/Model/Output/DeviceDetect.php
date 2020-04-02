<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

use Amasty\PageSpeedOptimizer\Lib\MobileDetect;

class DeviceDetect extends MobileDetect
{
    const DESKTOP = 'desktop';
    const TABLET = 'tablet';
    const MOBILE = 'mobile';

    /**
     * @var string
     */
    private $webPBrowsersString = '/(Edge|Firefox|Chrome|Opera)/i';

    /**
     * @inheritdoc
     */
    public function getDeviceType()
    {
        if ($this->isTablet()) {
            return self::TABLET;
        }
        if ($this->isMobile()) {
            return self::MOBILE;
        }

        return self::DESKTOP;
    }

    /**
     * @inheritdoc
     */
    public function isUseWebP()
    {
        $userAgent = $this->getUserAgent();

        if (preg_match($this->webPBrowsersString, $userAgent)) {
            return true;
        }

        return false;
    }
}
