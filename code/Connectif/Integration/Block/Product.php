<?php

namespace Connectif\Integration\Block;

use Connectif\Integration\Helper\ProductHelper;
use Magento\Framework\Escaper;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface as JsonEncoder;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface as UrlEncoder;
use Magento\Store\Model\ScopeInterface;
use Connectif\Integration\Helper\ConfigHelper;

/**
 * Product block used for outputting meta-data on the stores product pages.
 * This meta-data is sent to Connectif via JavaScript when users are browsing the
 * pages in the store.
 */
class Product extends View
{

    protected $_productHelper;
    protected $_escaper;
    protected $_configHelper;

    /**
     * Constructor.
     *
     * @param Context $context the context.
     * @param UrlEncoder $urlEncoder the  url encoder.
     * @param JsonEncoder $jsonEncoder the json encoder.
     * @param StringUtils $string the string lib.
     * @param \Magento\Catalog\Helper\Product $productHelper the product helper.
     * @param ConfigInterface $productTypeConfig the product type config.
     * @param FormatInterface $localeFormat the locale format.
     * @param Session $customerSession the user session.
     * @param ProductRepositoryInterface $productRepository th product repository.
     * @param PriceCurrencyInterface $priceCurrency the price currency.
     * @param array $data optional data.
     */
    public function __construct(
        Context $context,
        UrlEncoder $urlEncoder,
        JsonEncoder $jsonEncoder,
        StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        ProductHelper $cnProductHelper,
        Escaper $escaper,
        ConfigHelper $configHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
        $this->_cnProductHelper = $cnProductHelper;
        $this->_escaper = $escaper;
        $this->_configHelper = $configHelper;
    }

    /**
     * Render HTML for the page type if the module is enabled for the current
     * store.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $isEnabled = $this->_configHelper->isEnabled(ScopeInterface::SCOPE_STORE);
        if (!$isEnabled) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Returns the product meta data to tag.
     */
    public function getProductData()
    {
        return $this->_cnProductHelper->getProductData($this->getProduct());
    }

    public function getHtmlEscaper()
    {
        return $this->_escaper;
    }
}
