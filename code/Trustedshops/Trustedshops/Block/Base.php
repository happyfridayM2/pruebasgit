<?php
/**
 * @category  Trustedshops
 * @package   Trustedshops\Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */

namespace Trustedshops\Trustedshops\Block;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Trustedshops\Trustedshops\Model\System\Mode;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class Base extends Template
{
    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * list of supported locales
     *
     * @var array
     */
    protected $supportedLocales = [
        'de_DE',
        'en_GB',
        'fr_FR',
        'es_ES',
        'it_IT',
        'nl_NL',
        'pl_PL',
    ];

    /**
     * locale to use if no appropriate one was found
     *
     * @var string
     */
    protected $fallbackLocale = 'en_GB';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $context->getScopeConfig();
        $this->registry = $registry;
    }

    /**
     * get configuration value by field and area
     *
     * @param string $field
     * @param string $group
     * @return mixed
     */
    public function getConfig($field, $group)
    {
        return $this->config->getValue("trustedshops_trustedshops/{$group}/{$field}", ScopeInterface::SCOPE_STORE);
    }

    /**
     * check if the extension has a trustedshops id
     *
     * @return bool
     */
    public function isActive()
    {
        $tsId = $this->getTsId();
        if (!empty($tsId)) {
            return true;
        }
        return false;
    }

    /**
     * check if the mode is expert
     *
     * @return bool
     */
    public function isExpert()
    {
        return (Mode::MODE_EXPERT == $this->getConfig('mode', 'general'));
    }

    /**
     * get the trustedshops id
     *
     * @return string
     */
    public function getTsId()
    {
        return $this->getConfig('tsid', 'general');
    }

    /**
     * replace variables in expert codes
     *
     * @param string $code
     * @return string
     */
    public function replaceVariables($code)
    {
        $vars = [
            'tsid' => $this->getTsId(),
            'sku' => $this->getProductSku()
        ];

        foreach ($vars as $_placeholder => $_replaceValue) {
            $code = str_replace('%' . $_placeholder . '%', $_replaceValue, $code);
        }

        return $code;
    }

    /**
     * get the current shop locale
     *
     * @return string
     */
    public function getLocale()
    {
        $shopLocale = $this->config->getValue('general/locale/code');

        if (in_array($shopLocale, $this->supportedLocales)) {
            return $shopLocale;
        }

        // find base locale
        $localeParts = explode('_', $shopLocale);
        foreach ($this->supportedLocales as $supportedLocale) {
            if (strpos($supportedLocale, $localeParts[0]) !== false) {
                return $supportedLocale;
            }
        }

        return $this->fallbackLocale;
    }

    /**
     * get the current product sku
     *
     * @param bool $withChildren
     *
     * @return string
     */
    public function getProductSku($withChildren = false)
    {
        /**
         * @var $product Product
         */
        $product = $this->registry->registry('current_product');
        if (!$product || !$product->getSku()) {
            return '';
        }

        $productSkus = [];
        $productSkus[] = $product->getSku();

        $productType = $product->getTypeId();
        if ($withChildren && $productType === Grouped::TYPE_CODE) {
            $productSkus = [];
            $typeInstance = $product->getTypeInstance(true);
            $children = $typeInstance->getAssociatedProducts($product);

            foreach ($children as $_child) {
                $productSkus[] = $_child->getSku();
            }
        }

        return implode("','", $productSkus);
    }
}
