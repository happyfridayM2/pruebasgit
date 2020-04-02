<?php
/**
 * Created by PhpStorm.
 * User: migue
 * Date: 30/05/2017
 * Time: 16:56
 */

namespace Oct8ne\Oct8ne\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;

class CheckCredentials extends AbstractHelper
{

    protected $_context;
    protected $_scopeconfig;

    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->_context = $context;
        $this->_scopeconfig = $context->getScopeConfig();

    }

    /**
     * Comprueba si estas logeado o no
     */
    public function isLogged()
    {

        $email = $this->_scopeconfig->getValue("Oct8ne/user/email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $token = $this->_scopeconfig->getValue("Oct8ne/user/token", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $license = $this->_scopeconfig->getValue("Oct8ne/user/license", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);


        if (isset($email) && isset($token) && isset($license) && !empty($email) && !empty($token) && !empty($license)) {

            return true;

        } else return false;

    }
}