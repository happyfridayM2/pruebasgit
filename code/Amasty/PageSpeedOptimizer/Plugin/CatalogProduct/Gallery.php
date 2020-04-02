<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin\CatalogProduct;

use Amasty\PageSpeedOptimizer\Model\Image\OutputImage;
use Magento\Catalog\Block\Product\View\Gallery as ImageGallery;
use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Amasty\PageSpeedOptimizer\Model\Output\DeviceDetect;

class Gallery
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var DeviceDetect
     */
    private $deviceDetect;

    /**
     * @var OutputImage
     */
    private $outputImage;

    /**
     * @var string
     */
    private $deviceType;

    /**
     * @var bool
     */
    private $isWebpSupport = false;

    public function __construct(
        ConfigProvider $configProvider,
        DeviceDetect $deviceDetect,
        OutputImage $outputImage
    ) {
        $this->configProvider = $configProvider;
        $this->deviceDetect = $deviceDetect;
        $this->outputImage = $outputImage;
        $this->deviceType = $deviceDetect->getDeviceType();
        $this->isWebpSupport = $deviceDetect->isUseWebP();
    }

    /**
     * @param ImageGallery $subject
     * @param $result
     *
     * @return mixed
     * @throws \Zend_Json_Exception
     */
    public function afterGetGalleryImagesJson(ImageGallery $subject, $result)
    {
        if ($this->configProvider->isEnabled() && $this->deviceDetect->isUseWebP()) {
            $imagesSettings = \Zend_Json::decode($result);

            foreach ($imagesSettings as &$imagesSetting) {
                foreach ($imagesSetting as &$image) {
                    if (preg_match('/\.(jpg|jpeg|png|gif)$/', $image)) {
                        $image = $this->replaceWithBest($image);
                    }
                }
            }

            $result = \Zend_Json::encode($imagesSettings);
        }

        return $result;
    }

    /**
     * @param $imagePath
     *
     * @return mixed
     */
    private function replaceWithBest($imagePath)
    {
        $outputImage = $this->outputImage->setPath($imagePath);

        if ($outputImage->process()) {
            return $outputImage->getBest($this->deviceType, $this->isWebpSupport);
        }

        return $imagePath;
    }
}
