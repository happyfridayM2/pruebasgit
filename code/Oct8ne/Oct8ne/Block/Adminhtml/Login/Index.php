<?php
namespace Oct8ne\Oct8ne\Block\Adminhtml\Login;

use \Oct8ne\Oct8ne\Helper\CheckCredentials;
use \Magento\Framework\View\Element\Template\Context;


class Index extends \Magento\Framework\View\Element\Template
{


    protected $_check;

    protected $_scopeConfig;

    protected $_context;

    public function __construct(Context $context, CheckCredentials $check)
    {
        parent::__construct($context);

        $this->_check = $check;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_context = $context;

    }


    /**
     * Obtiene la url a un controlador
     * @return string
     */
    public function getFormAction()
    {

        return $this->getUrl('oct8ne/login/index');
    }


    /**
     * Comprueba si estas logeado o no
     */
    public function isLogged()
    {
        return $this->_check->isLogged();
    }

    /**
     * Get user email
     * @return mixed
     */
    public function getEmail()
    {
        return $this->_scopeConfig->getValue("Oct8ne/user/email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     * Get search engine
     * @return mixed
     */
    public function getSearchEngine() {

        return $this->_scopeConfig->getValue("Oct8ne/user/search_engine", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

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

}