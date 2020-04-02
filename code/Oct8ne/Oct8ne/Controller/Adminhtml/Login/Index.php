<?php

/**
 * Created by PhpStorm.
 * User: migue
 * Date: 25/05/2017
 * Time: 9:18
 */

namespace Oct8ne\Oct8ne\Controller\Adminhtml\Login;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Config\Model\ResourceModel\Config;
use \Magento\Framework\HTTP\Client\Curl;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Cache\TypeListInterface;

class Index extends \Magento\Backend\App\Action {
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $messageManager;
    protected $resourceConfig;
    protected $curl;
    protected $storeManagerInterface;
    protected $_cacheTypeList;
    
//https://magento.stackexchange.com/questions/78457/how-to-get-value-from-core-config-data-table-in-magento-2

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, Config $resourceConfig, Curl $curl, StoreManagerInterface $StoreManagerInterface, TypeListInterface $cacheTypeList)
    {

        parent::__construct($context);

        $this->request = $context->getRequest();

        $this->resultPageFactory = $resultPageFactory;

        $this->messageManager = $context->getMessageManager();

        $this->resourceConfig = $resourceConfig;

        $this->storeManagerInterface = $StoreManagerInterface;

        $this->curl = $curl;

        $this->_cacheTypeList = $cacheTypeList;

    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */

    public function execute()
    {

        $resultPage = null;

        $action = $this->request->getParam("oct8ne_form_action", "index");


        if ($action == "login") {


            //obtener los parametros de la peticion
            $email = $action = $this->request->getParam("oct8ne_user", "");
            $pass = $action = $this->request->getParam("oct8ne_pass", "");

            $this->linkUp($email, $pass);

            $this->cleanCache();
            $this->request->clearParams();
            //$resultPage = $this->resultPageFactory->create();


            /* Do your controller action stuff here */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('oct8ne/login/index');
            return $this->redirect();


        } else if ($action == "logout") {


            $this->request->clearParams();

            $this->resourceConfig->deleteConfig("Oct8ne/user/email", "default", 0);
            $this->resourceConfig->deleteConfig("Oct8ne/user/token", "default", 0);
            $this->resourceConfig->deleteConfig("Oct8ne/user/license", "default", 0);
            $this->resourceConfig->deleteConfig("Oct8ne/user/server", "default", 0);
            $this->resourceConfig->deleteConfig("Oct8ne/user/static", "default", 0);

            $this->cleanCache();

            //C$resultPage = $this->resultPageFactory->create();

            $this->messageManager->addSuccessMessage("Desconectado correctamente");

            return $this->redirect();


        } else if ($action == "basic_configuration") {


            $engine = $this->request->getParam("engine", "Magento");
            $position = $this->request->getParam("position", "Footer");

            $this->request->clearParams();

            $this->resourceConfig->saveConfig("Oct8ne/user/search_engine", $engine, "default", 0);
            $this->resourceConfig->saveConfig("Oct8ne/user/position", $position, "default", 0);

            $this->cleanCache();
            $this->messageManager->addSuccessMessage("Configuración actualizada correctamente correctamente");

            return $this->redirect();


        } else {

            $resultPage = $this->resultPageFactory->create();
        }

        $resultPage->getConfig()->getTitle()->set('Oct8ne configuration');
        return $resultPage;

    }


    /**
     * Redirecciona al login de nuevo
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function redirect()
    {

        /* Do your controller action stuff here */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('oct8ne/login/index');
        return $resultRedirect;
    }


    /**
     * Linkup hecho con magento
     * @param $user
     * @param $pass
     */
    private function linkUp($user, $pass)
    {


        //endpoint
        $url = 'https://backoffice.oct8ne.com/platformConnection/linkup';

        //datos
        $data = array('email' => $user,
            'pass' => $pass,
            'platform' => 'magento2',
            'urlDomain' => $this->storeManagerInterface->getStore()->getBaseUrl(), //$this->storeManagerInterface->getStore()->getBaseUrl(),
            'statusPlatform' => true);

        //opciones
        $options = array(

            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json;charset=UTF-8\r\n'),
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POSTFIELDS => json_encode($data)

        );

        //establece las opciones del curl
        $this->curl->setOptions($options);

        //hace la llamada
        $this->curl->get($url);

        //obiçtiene la respuesta
        $response = $this->curl->getBody();

        //to array
        $response = json_decode($response, true);


        //comprueba que los datos necesarios se han obtenido
        if ($response == null) {

            $this->messageManager->addErrorMessage("Unexpected error");
        } else {

            if ($response["ApiToken"] != null && $response["LicenseId"] != null) {

                //guardar en congiguracion
                $this->resourceConfig->saveConfig("Oct8ne/user/email", $user, "default", 0);
                $this->resourceConfig->saveConfig("Oct8ne/user/token", $response["ApiToken"], "default", 0);
                $this->resourceConfig->saveConfig("Oct8ne/user/license", $response["LicenseId"], "default", 0);

                $server = $response["Server"];
                $UrlStatic = $response["UrlStatic"];

                if (isset($server) && !empty($server)) {

                    $this->resourceConfig->saveConfig("Oct8ne/user/server", $server, "default", 0);

                } else {

                    $this->resourceConfig->saveConfig("Oct8ne/user/server", 'backoffice.oct8ne.com/', "default", 0);

                }

                if (isset($UrlStatic) && !empty($UrlStatic)) {

                    $this->resourceConfig->saveConfig("Oct8ne/user/static", $UrlStatic, "default", 0);

                } else {

                    $this->resourceConfig->saveConfig("Oct8ne/user/static", 'static.oct8ne.com/', "default", 0);
                }

                //mensaje correcto
                $this->messageManager->addSuccessMessage("Conectado correctamente");


            } else {

                //mensaje incorrecto
                $this->messageManager->addErrorMessage($response["Message"]);
            }

        }

    }


    /**
     * Limpiar cache
     */
    private function cleanCache()
    {
        $this->_cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        $this->_cacheTypeList->cleanType(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);
    }

}
