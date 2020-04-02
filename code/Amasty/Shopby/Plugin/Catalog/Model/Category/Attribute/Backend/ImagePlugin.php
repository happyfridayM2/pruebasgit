<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model\Category\Attribute\Backend;

use Magento\Catalog\Model\Category\Attribute\Backend\Image;
use Amasty\Shopby\Plugin\Catalog\Model\Category;

class ImagePlugin
{
    /**
     * Fix for bad urls on 2.3.4
     * @param Image $subject
     * @param \Closure $proceed
     * @param $category
     */
    public function aroundBeforeSave(Image $subject, \Closure $proceed, $category)
    {
        $attributeName = $subject->getAttribute()->getName();
        $thumbnailFiles = $category->getThumbnail();
        $imageFiles = $category->getImage();
        $proceed($category);

        if (isset($thumbnailFiles[0]['name']) && $attributeName == Category::THUMBNAIL) {
            $category->setThumbnail($thumbnailFiles[0]['name']);
        }

        if (isset($imageFiles[0]['name']) && $attributeName == 'image') {
            $category->setImage($imageFiles[0]['name']);
        }
    }
}
