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
use \Oct8ne\Oct8ne\Model\Oct8neHistoryFactory;
use \Magento\Quote\Api\CartRepositoryInterface;
use \Magento\Sales\Model\Order;
use \Magento\Framework\App\Config\ScopeConfigInterface;



class GetSalesReport extends \Magento\Framework\App\Action\Action
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
     * @var Oct8neHistoryFactory
     */
    protected $_oct8neHistory;

    /**
     * @var CartRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @var Order
     */
    protected $_order;


    /**
     * Inyeccion de dependencias. Inyectamos lo que nos hace falta
     * @param Context $context
     * @param ProductSummaryHelper $helper
     */

    public function __construct(Context $context, ResponseHelper $responseHelper, ScopeConfigInterface $scopeConfig, Oct8neHistoryFactory $oct8neHistoryFactory, CartRepositoryInterface $quoteRepository, Order $order)
    {

        $this->_context = $context;
        $this->_responseHelper = $responseHelper;
        $this->_oct8neHistory = $oct8neHistoryFactory;
        $this->_quoteRepository = $quoteRepository;

        $this->_scopeConfig = $scopeConfig;


        $this->_order = $order;


        parent::__construct($context);
    }

    /**
     * Devuelve todos los carritos creados con la ayuda de oct8ne creados entre las fechas solicitadas
     */
    public function execute()
    {

        $data = array();

        $from = $this->_context->getRequest()->getParam("from");
        $to = $this->_context->getRequest()->getParam("to");

        $apiToken = $this->_context->getRequest()->getParam("apiToken");
        $scope_token = $this->_scopeConfig->getValue("Oct8ne/user/token", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);


        if($apiToken != $scope_token)  return $this->_responseHelper->buildResponse($data);


        //control de rangos
        if(empty($from)) $from = "1970-01-01";
        if(empty($to)) $to = date("Y-m-d");

        //invierto el orden si se comete un error y se pone una fecha final menor o viceversa
        $from = $from . " 00:00:00";
        $to = $to . " 23:59:59";

        if($from > $to) {

            $aux = $to;
            $to = $from;
            $from = $aux;
        }

        //obtengo los carros
        $history = $this->_oct8neHistory->create();
        $collection = $history->getResourceCollection()
            ->addFieldToFilter('creation_time', ['gteq' => $from])
            ->addFieldToFilter('creation_time', ['lteq' => $to]);


        foreach ($collection as $item) {

            $salesItem = array();

            try {

                $id = $item->getData("cart_id");

                $quote = $this->_quoteRepository->get($id);

                $salesItem["quoteId"] = $id;

                $salesItem["sessionId"] = $item->getData("session_id");

                $salesItem["customerId"] = $quote->getCustomer()->getId();

                $salesItem["quoteId"] = $id;

                $salesItem["currency"] = $quote->getQuoteCurrencyCode(); //obtiene la moneda de pago
                $salesItem["price"] = $quote->getSubtotal(); //subtotal
                $salesItem["finalPrice"] = $quote->getGrandTotal(); //total
                $salesItem["itemsCount"] = $quote->getItemsSummaryQty(); //cantidad de productos en el carrito n*m
                $salesItem["productsCount"] = $quote->getItemsCount(); //cantidad de productos diferents

                $date_add = $quote->getCreatedAt();
                $date_upd = $quote->getUpdatedAt();

                $salesItem["lastAction"] = $date_add <= $date_upd ? "U" : "C";
                $salesItem["utcCreated"] = $date_add;
                $salesItem["utcLastUpdated"] = $date_upd;

                //solo para comprobar si hay o no order
                $order = $this->_order->getResourceCollection()->addFieldToFilter('quote_id', ['eq' => $id])->getFirstItem()->getId();

                if (!empty($order)) {
                    $salesItem["orderId"] = $order;
                }

                $data[] = $salesItem;

            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {

                //se ha lanzado una excepcion
            }

        }

        return $this->_responseHelper->buildResponse($data);

    }
}