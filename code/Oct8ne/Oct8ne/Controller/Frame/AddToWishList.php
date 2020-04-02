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
use \Magento\Customer\Model\Session;
use \Magento\Wishlist\Model\WishlistFactory;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Framework\Exception\NoSuchEntityException;

class AddToWishList extends \Magento\Framework\App\Action\Action
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
     * @var WishlistFactory
     */
    protected $_wishlistRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var Customer Session
     */
    protected $_customerSession;


    /**
     * Inyeccion de dependencias. Inyectamos lo que nos hace falta
     * @param Context $context
     * @param ProductSummaryHelper $helper
     */

    public function __construct(Context $context, ResponseHelper $responseHelper, WishlistFactory $wishlistRepository, ProductRepositoryInterface $productRepository, Session $customerSession)
    {
        $this->_context = $context;
        $this->_responseHelper = $responseHelper;
        $this->_wishlistRepository= $wishlistRepository;
        $this->_productRepository = $productRepository;
        $this->_customerSession = $customerSession;

        parent::__construct($context);
    }

    /**
     * Todos los controladores ejecutan el execute despues de la construccion
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $data = false;

        //usuario
        $customer = $this->_customerSession;

        //id de usuario
        $customerId = $customer->getId();

        //primero de to do comprobar si hay usuario
        if ($customerId != null) {

            //productIds
            $product_ids = $this->_context->getRequest()->getParam("productIds");


            $product_ids = explode(",", $product_ids);

            $wishlist = $this->_wishlistRepository->create()->loadByCustomerId($customerId, true);

            $items = $wishlist->getItemCollection();

            $products_in_wishlist = array();

            foreach ($items as $item){

                $products_in_wishlist[] = $item->getProductId();

            }

            foreach ($product_ids as $product_id) {

                try {
                    $product = $this->_productRepository->getById($product_id);
                } catch (NoSuchEntityException $e) {
                    $product = null;
                }

                if($product != null && !in_array($product_id, $products_in_wishlist)){

                    $wishlist->addNewItem($product);
                    $wishlist->save();

                    $data = true;
                }


            }
        }

        return $this->_responseHelper->buildResponse($data);

    }
}