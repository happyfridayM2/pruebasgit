<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model;

class ImageUploaderPlugin
{
    /**
     * Fix for magento 234
     * @param \Magento\Catalog\Model\ImageUploader $subject
     * @param string $path
     * @return string
     */
    public function beforeMoveFileFromTmp(\Magento\Catalog\Model\ImageUploader $subject, $path)
    {
        $posLastSlash = strripos($path, '/');

        return $posLastSlash && strpos($path, '/category/') !== false
            ? substr($path, $posLastSlash + 1)
            : $path;
    }
}
