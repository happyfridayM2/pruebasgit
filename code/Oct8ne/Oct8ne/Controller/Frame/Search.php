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
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Oct8ne\Oct8ne\Helper\Search\SearchFactory;
use \Magento\Store\Model\StoreManagerInterface;
use \Oct8ne\Oct8ne\Helper\Oct8neContextHelper;
use \Oct8ne\Oct8ne\Helper\ProductSummaryHelper;

class Search extends \Magento\Framework\App\Action\Action
{

    /**
     * @var ProductSummaryHelper|Oct8neModuleInfoHelper
     */
    protected $_helper;

    /**
     * @var Context
     */
    protected $_context;

    protected $_responseHelper;

    protected $_scopeConfig;

    protected $_searchFactory;

    protected $_storeManager;

    protected $_oct8neContext;

    protected $_productSummaryHelper;

    /**
     * Inyeccion de dependencias. Inyectamos lo que nos hace falta
     * @param Context $context
     * @param ProductSummaryHelper $helper
     */

    public function __construct(Context $context, ResponseHelper $responseHelper, ScopeConfigInterface $scopeConfig, SearchFactory $searchFactory, StoreManagerInterface $storeManager, Oct8neContextHelper $oct8neContextHelper, ProductSummaryHelper $productSummaryHelper)
    {
        $this->_context = $context;
        $this->_responseHelper = $responseHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_searchFactory = $searchFactory;
        $this->_storeManager = $storeManager;
        $this->_oct8neContext = $oct8neContextHelper;
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

        $data = array(
            'total' => 0,
            'results' => array(),
            'filters' => array(
                'applied' => array(),
                'available' => array()
            )
        );

        $engine = $this->_scopeConfig->getValue("Oct8ne/user/search_engine", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $storeId = $this->_context->getRequest()->getParam('store', $this->_storeManager->getStore()->getId());
        //$currency = $this->_context->getRequest()->getParam('currency', $this->_storeManager->getStore()->getCurrentCurrencyCode());
        $searchTerm = $this->_context->getRequest()->getParam('search', null);
        $searchOrder = $this->_context->getRequest()->getParam('orderby', 'relevance');
        $searchDir = $this->_context->getRequest()->getParam('dir', 'asc');

        $page = $this->_context->getRequest()->getParam("page", 1);

        if ($page < 1) {
            $page = 1;
        }

        $pageSize = $this->_context->getRequest()->getParam('pageSize', 10);

        $search_engine = $this->_searchFactory->getInstance($engine);

        $totalSearchResults = 0;
        $attrs_applied = array();
        $attrs_available = array();

        if ($search_engine->isValidSearchData($searchTerm, $storeId)) {

            $products = $search_engine->search($storeId, $searchTerm, $searchOrder, $searchDir, $page, $pageSize, $totalSearchResults, $attrs_applied, $attrs_available);

            $oct8necontext = $this->_oct8neContext->getOct8neContext();

            $data["total"] = $totalSearchResults;


            foreach ($products as $product){

                $data["results"][] = $this->_productSummaryHelper->getProductSummary($product,$oct8necontext);

            }

        }

        return $this->_responseHelper->buildResponse($data);

    }
}