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
use \Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use \Oct8ne\Oct8ne\Helper\Oct8neContextHelper;
use \Oct8ne\Oct8ne\Helper\ProductSummaryHelper;


class ProductRelated extends \Magento\Framework\App\Action\Action
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
     * @var ProductRepositoryInterfaceFactory
     */
    protected $_productRepository;

    /**
     * @var ProductSummaryHelper
     */
    protected $_productSummaryHelper;


    /**
     * ProductRelated constructor.
     * @param Context $context
     * @param ResponseHelper $responseHelper
     * @param ProductRepositoryInterfaceFactory $productRepositoryInterfaceFactory
     * @param Oct8neContextHelper $oct8necontext
     * @param ProductSummaryHelper $productSummaryHelper
     */
    public function __construct(Context $context, ResponseHelper $responseHelper, ProductRepositoryInterfaceFactory $productRepositoryInterfaceFactory, Oct8neContextHelper $oct8necontext, ProductSummaryHelper $productSummaryHelper)
    {
        $this->_context = $context;
        $this->_responseHelper = $responseHelper;
        $this->_productRepository = $productRepositoryInterfaceFactory;
        $this->_oct8necontext = $oct8necontext;
        $this->_productSummaryHelper = $productSummaryHelper;


        parent::__construct($context);
    }

    /**
     * Todos los controladores ejecutan el execute despues de la construccion
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $data = array();
        $products_related = array();


        $oct8ne_context = $this->_oct8necontext->getOct8neContext();

        //obtiene el valor de id del producto
        $product_id = $this->_context->getRequest()->getParam("productId");

        //control de si es numerico y mayor que 0
        if(!is_numeric($product_id) || $product_id <= 0) return $this->_responseHelper->buildResponse($data);

        $product = $this->_productRepository->create()->getById($product_id);

        //control de si se ha instanciado bien el objeto
        if(!isset($product_id)) return $this->_responseHelper->buildResponse($data);


        //obtenemos los productos
        $productsRelatedCollection = $product->getRelatedProductCollection()
                                    ->AddStoreFilter()
                                    ->addAttributeToSelect('id')
                                    ->addAttributeToSort('price', 'asc')
                                    ->setPageSize(20)
                                    ->setCurPage(1);

        $total = $productsRelatedCollection->count();

        $data["total"] = $total;


        //para cada producto de la coleccion llamamos a productsummary
        foreach ($productsRelatedCollection as $product){

            $products_related[] = $this->_productSummaryHelper->getProductSummary($product->getId(), $oct8ne_context);

        }

        $data["results"] = $products_related;


        return $this->_responseHelper->buildResponse($data);

    }
}