<?php
/**
 * Created by PhpStorm.
 * User: migue
 * Date: 29/05/2017
 * Time: 15:39
 */

namespace Oct8ne\Oct8ne\Block;

use \Oct8ne\Oct8ne\Helper\CheckCredentials;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Registry;



class Oct8neJS extends \Magento\Framework\View\Element\Template
{

    protected $_check;
    protected $_scopeConfig;
    protected $_registry;
    protected $_productFactory;
    protected $_storeManager;
    protected $_context;
    protected $_assetRepo;


    /**
     * Oct8neJS constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CheckCredentials $check
     */
    public function __construct(Context $context, CheckCredentials $check, Registry $registry)
    {
        parent::__construct($context);
        $this->_check = $check;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_registry = $registry;
        $this->_storeManager = $context->getStoreManager();
        $this->_context = $context;
        $this->_assetRepo = $context->getAssetRepository();

    }

    /**
     * Comprueba si estás logeado o no
     * Usa el helper
     * @return bool
     */
    public function isLogged()
    {

        return $this->_check->isLogged();

    }

    /**
     * Obtiene el codigo de licencia que está guardado en user/license
     * @return mixed
     */
    public function getLicense()
    {

        $license = $this->_scopeConfig->getValue("Oct8ne/user/license", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $license;
    }


    /**
     * Obtiene el servidor de oct8ne
     * @return mixed|string
     */
    public function getOct8neServer(){

        $server = $this->_scopeConfig->getValue("Oct8ne/user/server", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if(!isset($server) || empty($server)){ return "backoffice.oct8ne.com/"; }

        return $server;

    }

    /**
     * Devuelve la url estática de oct8ne
     * @return mixed|string
     */
    public function getUrlStatic(){

        $static = $this->_scopeConfig->getValue("Oct8ne/user/static", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if(!isset($static) || empty($static)){ return "static.oct8ne.com/"; }

        return $static;

    }

    /**
     * Posicion donde se carga el JS de magento
     * @return mixed|string
     */
    public function getPositionToLoad() {


        $position =  $this->_scopeConfig->getValue("Oct8ne/user/position", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if(!isset($position) || empty($position)){ return "Footer"; }

        return $position;

    }


    /**
     * Obtiene el email del usuario
     * @return mixed
     */
    public function getEmail()
    {
        return $this->_scopeConfig->getValue("Oct8ne/user/email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Obtener la url base de la tienda
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->cleanProtocol($this->_storeManager->getStore()->getBaseUrl());
    }

    /**
     * Obtiene la url de checkout
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->cleanProtocol($this->getUrl('checkout'));
    }

    /**
     * Obtiene la url de login
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->cleanProtocol($this->getUrl('customer/account/login'));
    }

    /**
     * Obtiene la url de pedido con exito
     * @return string
     */
    public function getSuccessUrl()
    {
        return $this->cleanProtocol($this->getUrl('checkout/onepage/success'));
    }

    /**
     * Obtener el idioma de la tienda
     * @return mixed
     */
    public function getLocale()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $localeCode = $this->_scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);

        $localeCode = str_replace("_", "-", $localeCode);

        return $localeCode;
    }

    /**
     * Obtiene la moneda de la tienda
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * Determina si la página es un producto o no
     * @return bool
     */
    public function isProductPage()
    {

        return $this->_context->getRequest()->getFullActionName() == "catalog_product_view";
    }

    /**
     * Devuelve la id del producto
     * @return int
     */
    public function getProductId()
    {

        $product = $this->_registry->registry('product');
        return $product->getId();
    }

    /**
     * Obtiene la imagen del producto
     * @return string
     */
    public function getProductThumbnail()
    {
        $product = $this->_registry->registry('product');

        if (!empty($product->getImage())) {

            return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
        } else {
            return $this->_assetRepo->getUrl("Oct8ne_Oct8ne::images/demo.jpg");
        }
    }

    /**
     * Elimina los protocolos
     * @param $string
     * @return mixed
     */
    public function cleanProtocol($string){

        $aux = str_replace("https:","", $string);
        $aux = str_replace("http:","", $aux);
        return $aux;


    }

}