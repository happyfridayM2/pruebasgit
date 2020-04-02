<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin\Swatches;

use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Amasty\PageSpeedOptimizer\Model\Image\OutputImage;
use Amasty\PageSpeedOptimizer\Model\Output\DeviceDetect;

class AjaxImage
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var OutputImage
     */
    private $outputImage;

    /**
     * @var DeviceDetect
     */
    private $deviceDetect;

    public function __construct(
        ConfigProvider $configProvider,
        OutputImage $outputImage,
        DeviceDetect $deviceDetect
    ) {
        $this->configProvider = $configProvider;
        $this->outputImage = $outputImage;
        $this->deviceDetect = $deviceDetect;
    }

    public function afterGetProductMediaGallery(\Magento\Swatches\Helper\Data $helper, $result)
    {
        if ($this->configProvider->isEnabled() && $this->configProvider->isReplaceImagesUsingUserAgent()
            && $this->deviceDetect->isUseWebP() && !empty($result)
        ) {
            $this->processItem($result);
            if (!empty($result['gallery'])) {
                foreach ($result['gallery'] as &$item) {
                    $this->processItem($item);
                }
            }
        }

        return $result;
    }

    public function processItem(&$item)
    {
        foreach (['large', 'medium', 'small'] as $key) {
            if (!empty($item[$key])) {
                $item[$key] = $this->outputImage->setPath($item[$key])->getWebpPath() ? : $item[$key];
            }
        }
    }
}
