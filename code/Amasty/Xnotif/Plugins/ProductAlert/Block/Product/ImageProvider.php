<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\ProductAlert\Block\Product;

use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Block\Product\Image;

/**
 * Class ImageProvider
 */
class ImageProvider
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ImageBuilder
     */
    private $imageBuilder;

    public function __construct(ImageBuilder $imageBuilder, Registry $registry)
    {
        $this->registry = $registry;
        $this->imageBuilder = $imageBuilder;
    }

    /**
     * @param \Magento\Framework\App\State $subject
     * @param \Closure $proceed
     * @param Product $product
     * @param string $imageId
     * @param array $attributes
     *
     * @return Image
     */
    public function aroundGetImage(
        \Magento\ProductAlert\Block\Product\ImageProvider $subject,
        \Closure $proceed,
        Product $product,
        $imageId,
        $attributes = []
    ) {
        if ($this->registry->registry('xnotif_test_notification')
            || $this->registry->registry('amasty_store_id')
        ) {
            //skip emulation because it was started into product alert email model
            return $this->imageBuilder->create($product, $imageId, $attributes);
        }

        return $proceed($product, $imageId, $attributes);
    }
}
