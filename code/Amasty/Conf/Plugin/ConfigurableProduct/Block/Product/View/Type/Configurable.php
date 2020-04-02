<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\ConfigurableProduct\Block\Product\View\Type;

use Amasty\Conf\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as NativeConfigurable;

class Configurable
{
    /**
     * @var array
     */
    private $allProducts = [];

    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param NativeConfigurable $subject
     * @return array|null
     */
    public function beforeGetAllowProducts(NativeConfigurable $subject)
    {
        if (!$subject->hasAllowProducts() && $this->helper->getModuleConfig('general/show_out_of_stock')) {
            $subject->setAllowProducts($this->getAllProducts($subject));
        }

        return $subject->getData('allow_products');
    }

    /**
     * @param NativeConfigurable $subject
     * @return array
     */
    private function getAllProducts(NativeConfigurable $subject)
    {
        $productId = $subject->getProduct()->getId();
        if (!isset($this->allProducts[$productId])) {
            $products = [];
            $allProducts = $subject->getProduct()->getTypeInstance(true)
                ->getUsedProducts($subject->getProduct());
            foreach ($allProducts as $product) {
                if ($product->getStatus() == Status::STATUS_ENABLED) {
                    $products[] = $product;
                }
            }
            $this->allProducts[$productId] = $products;
        }

        return $this->allProducts[$productId];
    }
}
