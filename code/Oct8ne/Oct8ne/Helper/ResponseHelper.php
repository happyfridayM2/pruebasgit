<?php
/**
 * Created by PhpStorm.
 * User: migue
 * Date: 05/06/2017
 * Time: 17:25
 */

namespace Oct8ne\Oct8ne\Helper;

use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\Controller\Result\RawFactory;


class ResponseHelper extends AbstractHelper
{

    /**
     * Contexto
     * @var Context
     */
    protected $_context;

    protected $_jsonfactory;

    protected $_rawFactory;


    /**
     * ResponseHelper constructor.
     * @param Context $context
     */
    public function __construct(Context $context, JsonFactory $resultJsonFactory, RawFactory $rawFactory)
    {
        parent::__construct($context);

        $this->_context = $context;

        $this->_jsonfactory = $resultJsonFactory;

        $this->_rawFactory = $rawFactory;

    }

    /**
     * Crea la respuesta en el formato adecuado
     * @param $data
     */
    public function buildResponse($data){

        $callback = $this->_context->getRequest()->getParam('callback', NULL);

        if ($callback != null) {

            $data = json_encode($data);
            $data = $callback . "(" . $data . ");";
            $result = $this->_rawFactory->create();
            $result->setContents($data);
            $result->setHeader('Content-type','application/javascript',true);
            return $result;


        } else {

            $result = $this->_jsonfactory->create();
            $result->setData($data);
            return $result;
        }

    }

}