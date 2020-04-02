<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\Product\View\Type;

use Amasty\Conf\Helper\Data;
use Amasty\Conf\Model\Source\MatrixMode;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as TypeConfigurable;
use Magento\Swatches\Block\Product\Renderer\Listing\Configurable as ListingConfigurable;
use Amasty\Conf\Model\Source\Preselect;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableModel;
use Magento\Catalog\Model\Product;

class Configurable
{
    const PLUGIN_TYPE_PRODUCT = 'product';
    const PLUGIN_TYPE_CATEGORY = 'category';
    const AMASTY_BACKORDER_CODE = '101';

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    private $output;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * @var ConfigurableModel
     */
    private $configurableModel;

    /**
     * @var array
     */
    private $preselectedInfo = [];

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Conf\Model\ResourceModel\Inventory
     */
    private $inventory;

    public function __construct(
        Data $helper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Output $output,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        ConfigurableModel $configurableModel,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Conf\Model\ResourceModel\Inventory $inventory
    ) {
        $this->helper = $helper;
        $this->layoutFactory = $layoutFactory;
        $this->output = $output;
        $this->coreRegistry = $registry;
        $this->stockRegistry = $stockRegistry;
        $this->configurableModel = $configurableModel;
        $this->moduleManager = $moduleManager;
        $this->storeManager = $storeManager;
        $this->inventory = $inventory;
    }

    /**
     * @param TypeConfigurable $subject
     * @param $result
     * @return string
     */
    public function afterGetJsonConfig(
        TypeConfigurable $subject,
        $result
    ) {
        $availableNames = ['product.info.options.configurable', 'product.info.options.swatches'];
        if ($result && in_array($subject->getNameInLayout(), $availableNames)) {
            $config = $this->helper->decode($result);

            $config['product_information'] = $this->_getProductsInformation($subject);
            $config['show_prices'] = $this->helper->getModuleConfig('general/show_price');
            $config['show_dropdown_prices'] = $this->helper->getModuleConfig('general/dropdown_price');
            $config['change_mouseover'] = $this->helper->getModuleConfig('general/change_mouseover');
            $config['show_out_of_stock'] = $this->crossOutOfStock();
            $config['swatches_slider'] = boolval($this->helper->getModuleConfig('general/swatches_slider'));
            $config['swatches_slider_items_per_view']
                = $this->helper->getModuleConfig('general/swatches_slider_items_per_view');

            $config['matrix'] = $this->isMatrixEnabled($subject->getProduct());
            $config['titles'] = $config['matrix'] ? $this->getMatrixTitles() : [];

            $preselect = $this->helper->getModuleConfig('preselect/preselect');
            if ($preselect || $subject->getProduct()->getSimplePreselect()) {
                $preselectedData = $this->getPreselectData($preselect, $subject);
                if ($preselectedData['product']) {
                    $config['preselect']['product_id'] = $preselectedData['product']->getId();
                    $config['preselect']['attributes'] = $preselectedData['attributes'];
                }
            }

            $result = $this->helper->encode($config);
        }

        if ($subject instanceof ListingConfigurable) {
            $config = $this->helper->decode($result);
            $config['change_mouseover'] = $this->helper->getModuleConfig('general/change_mouseover');
            $config['show_out_of_stock'] = $this->crossOutOfStock();
            $config['product_information'] = $this->_getProductsInformation($subject, self::PLUGIN_TYPE_CATEGORY);
            $preselect = $this->helper->getModuleConfig('preselect/preselect');
            $categoryPreselect = $this->helper->getModuleConfig('preselect/preselect_category');
            if (($preselect || $subject->getProduct()->getSimplePreselect())
                && $categoryPreselect
            ) {
                $preselectedData = $this->getPreselectData($preselect, $subject);
                if ($preselectedData['product']) {
                    $config['preselect']['attributes'] = $preselectedData['attributes'];
                    $config['preselect']['product_id'] = $preselectedData['product']->getId();
                    $config['blockedImage'] = true;
                }
            }
            $config['preselected'] = false;
            $result = $this->helper->encode($config);
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function crossOutOfStock()
    {
        return !$this->moduleManager->isEnabled('Amasty_Xnotif') //customers should select option
            && $this->helper->getModuleConfig('general/show_out_of_stock');
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function isMatrixEnabled(\Magento\Catalog\Model\Product $product)
    {
        $setting = $this->helper->getModuleConfig('matrix/enable');

        return $setting == MatrixMode::YES_FOR_ALL
            || ($setting == MatrixMode::YES && $product->getData(Data::MATRIX_ATTRIBUTE));
    }

    /**
     * public access for Amasty_HidePrice
     *
     * @return array
     */
    public function getMatrixTitles()
    {
        $result = [
            'attribute' => __('Option'),
            'price' => __('Price'),
            'sku' => __('SKU'),
            'available' => __('Available'),
            'qty' => __('Qty'),
            'subtotal' => __('Subtotal')
        ];

        if (!$this->isShowQtyAvailable()) {
            unset($result['available']);
        }

        if (!$this->isShowSubtotal()) {
            unset($result['subtotal']);
        }

        if (!$this->isSkuDisplayed()) {
            unset($result['sku']);
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function isShowQtyAvailable()
    {
        return (bool)$this->helper->getModuleConfig('matrix/available_qty');
    }

    /**
     * @return bool
     */
    private function isShowSubtotal()
    {
        return (bool)$this->helper->getModuleConfig('matrix/subtotal');
    }

    /**
     * @return bool
     */
    private function isSkuDisplayed()
    {
        return (bool)$this->helper->getModuleConfig('matrix/display_sku');
    }

    /**
     * @param TypeConfigurable $subject
     * @return array
     */
    protected function _getProductsInformation(TypeConfigurable $subject, $type = self::PLUGIN_TYPE_PRODUCT)
    {
        $info = [];
        $reloadValues = $this->helper->getModuleConfig('reload/content');
        $reloadValues = explode(',', $reloadValues);

        $info['default'] = $this->_getProductInfo($subject->getProduct(), $reloadValues, $type);

        return $this->addProductsInfo($info, $subject, $reloadValues, $type);
    }

    /**
     * @param array $info
     * @param TypeConfigurable $subject
     * @param $reloadValues
     * @param $type
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function addProductsInfo(array $info, TypeConfigurable $subject, $reloadValues, $type)
    {
        $products = $subject->getAllowProducts();
        $productsSku = $this->getProductsSku($products);
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $stockValues = $this->inventory->getStocks($productsSku, $websiteCode);
        $qtyValues = $this->inventory->getQty($productsSku, $websiteCode);

        foreach ($products as $product) {
            $info[$product->getId()] = $this->_getProductInfo($product, $reloadValues, $type);
            $info[$product->getId()]['is_in_stock'] = (int)($stockValues[$product->getSku()] ?? 0);
            $info[$product->getId()]['qty'] = (float)($qtyValues[$product->getSku()] ?? 0);
        }

        return $info;
    }

    /**
     * @param array $products
     * @return array
     */
    private function getProductsSku($products = [])
    {
        $data = [];
        foreach ($products as $product) {
            $data[$product->getId()] = $product->getSku();
        }

        return $data;
    }

    /**
     * @param $product
     * @param $reloadValues
     * @return array
     */
    protected function _getProductInfo($product, $reloadValues, $type)
    {
        $productInfo = [];
        if ($type == self::PLUGIN_TYPE_PRODUCT && !in_array('none', $reloadValues)) {
            $layout = $this->layoutFactory->create();

            foreach ($reloadValues as $reloadValue) {
                $selector = $this->helper->getModuleConfig('reload/' . $reloadValue);
                if (!$selector) {
                    continue;
                }
                if ($reloadValue == 'attributes') {
                    $block = $layout->createBlock(
                        \Magento\Catalog\Block\Product\View\Attributes::class,
                        'product.attributes',
                        ['data' => []]
                    )->setTemplate('product/view/attributes.phtml');

                    $currentProduct = $this->coreRegistry->registry('product');
                    $this->coreRegistry->unregister('product');
                    $this->coreRegistry->register('product', $product);

                    $value = $block->setProduct($product)->toHtml();

                    $this->coreRegistry->unregister('product');
                    $this->coreRegistry->register('product', $currentProduct);
                } else {
                    $value = $this->output->productAttribute($product, $product->getData($reloadValue), $reloadValue);
                }
                if ($value) {
                    $productInfo[$reloadValue] = [
                        'selector' => $selector,
                        'value' => $value
                    ];
                }
            }
        }

        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $sku = $product->getData('sku');

        if ($this->helper->isPreorderEnabled($product)) {
            $stockItem = $this->inventory->getStockItem($sku, $websiteCode);
            $productInfo['preorder'] = $stockItem->getBackorders() == self::AMASTY_BACKORDER_CODE;
        } else {
            $productInfo['preorder'] = false;
        }

        $productInfo['sku_value'] = $sku;

        return $productInfo;
    }

    /**
     * @param integer $preselect
     * @param TypeConfigurable $subject
     * @return array
     */
    public function getPreselectData($preselect, $subject)
    {
        $productId = $subject->getProduct()->getId();
        if (!isset($this->preselectedInfo[$productId])) {
            $selectedProduct = $this->getSimplePreselectedChild($subject);
            if (!$selectedProduct) {
                $selectedProduct = $this->getPreselectByOption($preselect, $subject);
            }

            $this->preselectedInfo[$productId] = [
                'attributes' => $this->getAttributesForProduct($subject->getAllowAttributes(), $selectedProduct),
                'product' => $selectedProduct
            ];
        }

        return $this->preselectedInfo[$productId] ?? null;
    }

    /**
     * @param TypeConfigurable $subject
     *
     * @return Product|null
     */
    protected function getSimplePreselectedChild(TypeConfigurable $subject)
    {
        $selectedProduct = null;
        if ($sku = $subject->getProduct()->getSimplePreselect()) {
            foreach ($subject->getAllowProducts() as $product) {
                if ($product->getSku() == $sku) {
                    $selectedProduct = $product;
                    break;
                }
            }
        }

        return $selectedProduct;
    }

    /**
     * @param $preselect
     * @param TypeConfigurable $subject
     *
     * @return Product|null
     */
    protected function getPreselectByOption($preselect, TypeConfigurable $subject)
    {
        switch ($preselect) {
            case Preselect::FIRST_OPTIONS:
                $selectedProduct = $this->getFirstOptionProduct($subject);
                break;
            case Preselect::CHEAPEST:
                $selectedProduct = $this->getCheapestProduct($subject->getAllowProducts());
                break;
            default:
                $selectedProduct = null;
        }

        return $selectedProduct;
    }

    /**
     * @param TypeConfigurable $subject
     *
     * @return Product|null
     */
    protected function getFirstOptionProduct(TypeConfigurable $subject)
    {
        $lastRow = count($subject->getAllowAttributes()) - 1;
        $selectedIdsOptions = [];
        foreach ($subject->getAllowAttributes() as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();
            if (count($selectedIdsOptions) == $lastRow) {
                foreach (($attribute['options'] ?? []) as $option) {
                    if (isset($option['value_index'])) {
                        $selectedIdsOptions[$attributeId] = $option['value_index'];
                        $selectedProduct = $this->configurableModel->getProductByAttributes(
                            $selectedIdsOptions,
                            $subject->getProduct()
                        );
                        if ($selectedProduct) {
                            break;
                        }
                    }
                }
            } elseif (isset($attribute['options'][0]['value_index'])) {
                $selectedIdsOptions[$attributeId] = $attribute['options'][0]['value_index'];
            }
        }

        return $selectedProduct;
    }

    /**
     * @param array $allowedAttributes
     * @param Product $product
     * @return array
     */
    private function getAttributesForProduct($allowedAttributes, $product)
    {
        $selectedOptions = [];
        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getProductAttribute()->getAttributeCode();
            if ($attributeValue = $product->getData($attributeCode)) {
                $selectedOptions[$attributeCode] = $attributeValue;
            }
        }

        return $selectedOptions;
    }

    /**
     * Return product with minimal price
     *
     * @param $allowedProducts
     * @return null|Product
     */
    private function getCheapestProduct($allowedProducts)
    {
        $selectedProduct = null;

        $this->coreRegistry->unregister('hideprice_off');
        $this->coreRegistry->register('hideprice_off', true);
        foreach ($allowedProducts as $product) {
            if (!$selectedProduct
                || $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue()
                < $selectedProduct->getPriceInfo()->getPrice('final_price')->getAmount()->getValue()
            ) {
                $selectedProduct = $product;
            }
        }
        $this->coreRegistry->unregister('hideprice_off');

        return $selectedProduct;
    }
}
