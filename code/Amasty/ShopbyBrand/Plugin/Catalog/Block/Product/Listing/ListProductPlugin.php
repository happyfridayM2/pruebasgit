<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Plugin\Catalog\Block\Product\Listing;

use Amasty\ShopbyBrand\Plugin\Catalog\Block\Product\View\BlockHtmlTitlePlugin;
use Magento\Framework\Registry;
use Amasty\ShopbyBrand\Model\Source\Tooltip;

/**
 * Class ListProductPlugin
 *
 * @package Amasty\ShopbyBrand\Plugin\Catalog\Block\Product\Listing
 */
class ListProductPlugin
{
    /**
     * @var BlockHtmlTitlePlugin
     */
    private $blockHtmlTitlePlugin;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $brandHelper;

    /**
     * @var null|\Magento\Catalog\Model\Product
     */
    private $originalProduct = null;

    public function __construct(
        BlockHtmlTitlePlugin $blockHtmlTitlePlugin,
        Registry $registry,
        \Amasty\ShopbyBrand\Helper\Data $brandHelper
    ) {
        $this->blockHtmlTitlePlugin = $blockHtmlTitlePlugin;
        $this->registry = $registry;
        $this->brandHelper = $brandHelper;
    }

    /**
     * @param $original
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function beforeGetProductDetailsHtml($original, \Magento\Catalog\Model\Product $product)
    {
        $this->setProduct($product);

        return [$product];
    }

    /**
     * TODO: Unit
     * Add Brand Label to List Page
     *
     * @param $original
     * @param $html
     * @return string
     */
    public function afterGetProductDetailsHtml($original, $html)
    {
        return $html . $this->getLogoHtml();
    }

    /**
     * @param \Amasty\Mostviewed\Block\Widget\Related $original
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function beforeGetBrandLogoHtml(
        \Amasty\Mostviewed\Block\Widget\Related $original,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->setProduct($product);
        return [$product];
    }

    /**
     * Add Brand Label to Amasty Related Block
     *
     * @param \Amasty\Mostviewed\Block\Widget\Related $original
     * @param $html
     * @return string
     */
    public function afterGetBrandLogoHtml(\Amasty\Mostviewed\Block\Widget\Related $original, $html)
    {
        return $html . $this->getLogoHtml();
    }

    /**
     * @return string
     */
    private function getLogoHtml()
    {
        $logoHtml = '';
        if ($this->isShowOnListing()) {
            $this->updateConfigurationData();

            $this->startEmulateProduct($this->getProduct());
            $logoHtml = $this->blockHtmlTitlePlugin->generateLogoHtml();
            $this->stopEmulateProduct();
        }

        return $logoHtml;
    }

    protected function updateConfigurationData()
    {
        $data = $this->blockHtmlTitlePlugin->getData();
        $data['show_short_description'] = false;
        $data['width'] = $this->brandHelper->getBrandLogoProductListingWidth();
        $data['height'] = $this->brandHelper->getBrandLogoProductListingHeight();
        $data['tooltip_enabled'] = $this->isTooltipEnabled();
        $this->blockHtmlTitlePlugin->setData($data);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function startEmulateProduct($product)
    {
        $this->originalProduct = $this->registry->registry('current_product') ?: null;
        $this->registry->unregister('current_product');
        $this->registry->register('current_product', $product);
    }

    protected function stopEmulateProduct()
    {
        $this->registry->unregister('current_product');
        if ($this->originalProduct) {
            $this->registry->register('current_product', $this->originalProduct);
            $this->originalProduct = null;
        }
    }

    /**
     * @return bool
     */
    protected function isShowOnListing()
    {
        return (bool) $this->brandHelper->getModuleConfig('product_listing_settings/show_on_listing');
    }

    /**
     * @return bool
     */
    private function isTooltipEnabled()
    {
        $tooltipSetting = explode(
            ',',
            $this->brandHelper->getModuleConfig('general/tooltip_enabled')
        );

        return in_array(Tooltip::LISTING_PAGE, $tooltipSetting);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }
}
