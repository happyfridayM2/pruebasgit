<?php
/**
 * Created by PhpStorm.
 * User: migue
 * Date: 10/06/2017
 * Time: 12:06
 */

namespace Oct8ne\Oct8ne\Controller\Frame;

use \Magento\Framework\App\Action\Context;
use \Oct8ne\Oct8ne\Helper\ResponseHelper;
use \Magento\Checkout\Model\Session;

class GetCart extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var ResponseHelper
     */
    protected $_responseHelper;

    /**
     * Cart
     * @var Session
     */
    protected $_session;

    /**
     * Constructor
     * @param Context $context
     * @param ProductSummaryHelper $helper
     */

    public function __construct(Context $context, ResponseHelper $responseHelper, Session $session)
    {
        $this->_context = $context;
        $this->_responseHelper = $responseHelper;

        $this->_session = $session;

        parent::__construct($context);
    }

    /**
     * Todos los controladores ejecutan el execute despues de la construccion
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $data = array();

        //obtiene el carrito de la sesioon
        $quote = $this->_session->getQuote();

        $id = $quote->getId();

        //si el carro esta vacio
        if($id == null)  return $this->_responseHelper->buildResponse($data);


        $data["currency"] = $quote->getQuoteCurrencyCode(); //obtiene la moneda de pago
        $data["price"] = $quote->getSubtotal(); //subtotal
        $data["finalPrice"] = $quote->getGrandTotal(); //total
        $data["totalItems"] = $quote->getItemsSummaryQty(); //cantidad de productos en el carrito n*m

        $items = $quote->getAllVisibleItems(); //lineas visibbles en el carrito

        $products = array();

        foreach($items as $item) {

            $id = $item->getProductId();
            $qty =  $item->getQty();
            $name = $item->getName();
            $price = $item->getPrice();

            $products[] = array("internalId" => $id, "title" => $name, "qty" => $qty, "price" => $price);
        }


        $data["cart"] = $products;

        return $this->_responseHelper->buildResponse($data);

    }
}