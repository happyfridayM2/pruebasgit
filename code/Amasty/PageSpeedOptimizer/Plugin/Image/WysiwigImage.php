<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Plugin\Image;

class WysiwigImage extends AbstractImage
{
    /**
     * @param $subject
     * @param $result
     *
     * @return string
     */
    public function afterUploadFile($subject, $result)
    {
        if ($this->configProvider->isEnabled() && $this->configProvider->isOptimizeImages()
            && $this->configProvider->isAutomaticallyOptimizeImages()
        ) {
            if ($image = $this->prepareFile($result['path'] . DIRECTORY_SEPARATOR . $result['file'])) {
                $this->imageProcessor->execute($image);
            }
        }

        return $result;
    }

    /**
     * @param $subject
     * @param $target
     */
    public function beforeDeleteFile($subject, $target)
    {
        $this->imageProcessor->removeDumpImage($target);
    }
}
