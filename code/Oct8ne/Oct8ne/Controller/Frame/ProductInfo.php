<?php

namespace Oct8ne\Oct8ne\Controller\Frame;

use \Oct8ne\Oct8ne\Helper\ProductSummaryHelper;
use \Oct8ne\Oct8ne\Helper\Oct8neContextHelper;
use \Magento\Framework\App\Action\Context;
use \Oct8ne\Oct8ne\Helper\ResponseHelper;


class ProductInfo extends \Magento\Framework\App\Action\Action
{
    protected $_helper;
    protected $_context;
    protected $_oct8necontext;
    protected $_responseHelper;


    /**
     * Inyeccion de dependencias. Inyectamos lo que nos hace falta
     * ProductInfo constructor.
     * @param Context $context
     * @param ProductSummaryHelper $helper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(Context $context, ProductSummaryHelper $helper, Oct8neContextHelper $oct8necontext, ResponseHelper $responseHelper)
    {
        $this->_context = $context;
        $this->_helper = $helper;
        $this->_oct8necontext = $oct8necontext;
        $this->_responseHelper = $responseHelper;

        parent::__construct($context);
    }

    /**
     * Todos los controladores ejecutan el execute despues de la construccion
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {


        $result = array();
        //obtener el contexto
        $oct8ne_context = $this->_oct8necontext->getOct8neContext();

        //esto obtiene el valor de una variable en la url
        $product_ids = $this->_context->getRequest()->getParam("productIds");


        $product_ids = explode(",", $product_ids);


        foreach ($product_ids as $product_id) {

            $aux = $this->_helper->getProductSummary($product_id, $oct8ne_context, true); //true le indica que añada mas informacion

            if (!empty($aux)) {

                $result[] = $aux;
            }
        }


        return $this->_responseHelper->buildResponse($result);

    }
}