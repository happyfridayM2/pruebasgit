<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\ConfigurableProduct;

/**
 * Class Data
 */
class Data extends \Magento\ConfigurableProduct\Helper\Data
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Amasty\Xnotif\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->imageHelper = $imageHelper;
        $this->moduleManager = $moduleManager;
        parent::__construct($imageHelper);
        $this->helper = $helper;
        $this->registry = $registry;
    }

    /**
     * Get Options for Configurable Product Options
     *
     * @param \Magento\Catalog\Model\Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        $aStockStatus = [];
        $allowAttributes = $this->getAllowAttributes($currentProduct);

        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            $key = [];
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());

                $options[$productAttributeId][$attributeValue][] = $productId;
                $options['index'][$productId][$productAttributeId] = $attributeValue;

                /*Amasty code start - code here for improving performance*/
                $key[] = $attributeValue;
            }

            if ($key && !$this->moduleManager->isEnabled('Amasty_Stockstatus')) {
                $saleable =  $this->helper->isItemSalable($product);

                $aStockStatus[implode(',', $key)] = [
                    'is_in_stock'   => $saleable,
                    'custom_status' => (!$saleable) ? __('Out of Stock') : '',
                    'product_id'    => $product->getId()
                ];
                if (!$saleable) {
                    $aStockStatus[implode(',', $key)]['stockalert'] =
                        $this->helper->getStockAlert($product);
                }

                $aStockStatus[implode(',', $key)]['pricealert'] =
                    $this->helper->getPriceAlert($product);
            }
            /*Amasty code end*/
        }
        $aStockStatus['is_in_stock'] = $this->helper->isItemSalable($currentProduct);

        $this->registry->unregister('amasty_xnotif_data');
        $this->registry->register('amasty_xnotif_data', $aStockStatus);

        return $options;
    }
}
