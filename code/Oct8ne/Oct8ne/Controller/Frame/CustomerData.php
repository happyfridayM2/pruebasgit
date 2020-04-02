<?php
/**
 * Created by PhpStorm.
 * User: migue
 * Date: 10/06/2017
 * Time: 12:06
 */

namespace Oct8ne\Oct8ne\Controller\Frame;

use \Magento\Framework\App\Action\Context;
use \Magento\Customer\Model\Session;
use \Magento\Checkout\Model\Session as Cart;
use \Oct8ne\Oct8ne\Helper\ProductSummaryHelper;
use \Oct8ne\Oct8ne\Helper\Oct8neContextHelper;
use \Magento\Wishlist\Controller\WishlistProviderInterface;


use \Oct8ne\Oct8ne\Helper\ResponseHelper;

class CustomerData extends \Magento\Framework\App\Action\Action
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
     * @var Customer Session
     */
    protected $_customerSession;

    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * @var ProductSummaryHelper
     */
    protected $_productSummary;

    /**
     * @var Oct8neContextHelper
     */
    protected $_oct8neContext;

    /**
     * @var WishlistProviderInterface
     */
    protected $_wishlist;


    /**
     * CustomerData constructor.
     * @param Context $context
     * @param ResponseHelper $responseHelper
     * @param Session $customerSession
     * @param Cart $cart
     * @param ProductSummaryHelper $productSummaryHelper
     * @param Oct8neContextHelper $oct8neContextHelper
     * @param WishlistProviderInterface $wishlistProvider
     */

    public function __construct(Context $context, ResponseHelper $responseHelper, Session $customerSession, Cart $cart, ProductSummaryHelper $productSummaryHelper, Oct8neContextHelper $oct8neContextHelper, WishlistProviderInterface $wishlistProvider)
    {
        $this->_context = $context;
        $this->_responseHelper = $responseHelper;
        $this->_customerSession = $customerSession;
        $this->_cart = $cart;
        $this->_productSummary = $productSummaryHelper;
        $this->_oct8neContext = $oct8neContextHelper;
        $this->_wishlist = $wishlistProvider;

        parent::__construct($context);
    }

    /**
     * Todos los controladores ejecutan el execute despues de la construccion
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $data = array();
        $cart = array();
        $wishlist = array();

        $oct8necontext = $this->_oct8neContext->getOct8neContext();

        $customer = $this->_customerSession;

        $id = $customer->getId();

        //Si hay usuario recojo los datos
        //if ($id == null) $this->_responseHelper->buildResponse($data);

        $data["id"] = $id;
        $data["firstName"] = $customer->getCustomer()->getFirstname();
        $data["lastName"] = $customer->getCustomer()->getLastname();
        $data["email"] = $customer->getCustomer()->getEmail();


        //obtener wishlist

        $wishlist_ = $this->_wishlist->getWishlist()->getItemCollection();

        foreach ($wishlist_ as $item) {

            $id = $item->getProductId();
            $wishlist[] = $this->_productSummary->getProductSummary($id, $oct8necontext);
        }
        //obtener cart

        //obtiene el carrito de la sesioon
        $quote = $this->_cart->getQuote();

        $id = $quote->getId();

        //si hay carrito
        if ($id != null) {

            $items = $quote->getAllVisibleItems(); //lineas visibbles en el carrito

            foreach ($items as $item) {

                $id = $item->getProductId();
                $qty = $item->getQty();

                $aux = $this->_productSummary->getProductSummary($id, $oct8necontext);

                if (!empty($aux)) {
                    $aux["qty"] = $qty;
                }

                $cart[] = $aux;
            }

        }

        //añado la lista de deseos
        $data["wishlist"] = $wishlist;

        //añado el carrito
        $data["cart"] = $cart;

        //lanzo la respuesta
        return $this->_responseHelper->buildResponse($data);

    }
}