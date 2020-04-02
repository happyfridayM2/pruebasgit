<?php
/**
 * Created by PhpStorm.
 * User: migue
 * Date: 01/06/2017
 * Time: 16:06
 */

namespace Oct8ne\Oct8ne\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Store\Model\Store;
use \Magento\Framework\Locale\Resolver;


/**
 * Define que tienda y moneda usar dependiendo del idioma y la moneda solicitada
 * Class Oct8neContextHelper
 * @package Oct8ne\Oct8ne\Helper
 */
class Oct8neContextHelper extends AbstractHelper
{

    protected $_context;
    protected $_storeManager;
    protected $_store;
    protected $_resolver;
    protected $_scopeconfig;

    public function __construct(Context $context, StoreManagerInterface $storeManager, Store $store, Resolver $resolver)
    {
        parent::__construct($context);

        $this->_context = $context;
        $this->_storeManager = $storeManager;
        $this->_store = $store;
        $this->_scopeconfig = $context->getScopeConfig();
        $this->_resolver = $resolver;
    }

    public function getOct8neContext()
    {


        $oct8ne_context = array();

        $oct8ne_context["context_shop"] = $this->_storeManager->getStore()->getId();
        $oct8ne_context["context_currency"] = $this->_store->getCurrentCurrencyCode();
        $oct8ne_context["current_currency"] = $this->_scopeconfig->getValue('currency/options/base', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);


        $locale = $this->_context->getRequest()->getParam("locale", null);
        $currency = $this->_context->getRequest()->getParam("currency", null);

        if ($locale) {

            $locale = str_replace("-", "_", $locale);

            $current = $this->_resolver->getLocale();

            if ($locale != $current) {


                $shops = $this->_storeManager->getStores();

                $store_locales = array();
                foreach ($shops as $shop) {

                    $store_locales[$shop->getName()] = array();
                    $store_locales[$shop->getName()]["id"] = $shop->getId();
                    $store_locales[$shop->getName()]["lang"] = $this->_scopeconfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $shop->getId());

                }

                $store_locale = array_filter($store_locales, function ($aux) use ($locale) {

                    return $aux["lang"] == $locale;

                });

                $store_locale = reset($store_locale);

                if (isset($store_locale) && !empty($store_locale)) {
                    $oct8ne_context["context_shop"] = $store_locale["id"];
                }

            }

        }


        if ($currency) {

            $current = $this->_store->getCurrentCurrencyCode();

            if ($current != $currency) {

                if (in_array($currency, $this->_store->getAvailableCurrencyCodes())) {

                    $oct8ne_context["context_currency"] = $currency;

                };
            }

        }

        return $oct8ne_context;

    }
}