<?php
/**
 * Created by PhpStorm.
 * User: migue
 * Date: 20/06/2017
 * Time: 19:16
 */

namespace Oct8ne\Oct8ne\Helper\Search;

use \Magento\CatalogSearch\Helper\Data as CatalogSearch;
use \Magento\Catalog\Model\Layer\Category\FilterableAttributeList;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Framework\Api\Search\SearchCriteriaBuilder;
use \Magento\Framework\Api\FilterBuilder;
use \Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SortOrder;
use \Magento\CatalogInventory\Model\Stock\StockItemRepository;
use \Magento\Search\Api\SearchInterface;
use \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as FullText;
use Magento\Framework\Api\Filter;
/*
 * Motor de busqueda por defecto (Magento)
 */

class Magento extends Base
{


    protected $_catalogSearchHelper;

    protected $_filterableAttributeList;

    protected $_productRepository;

    protected $_searchCriteriaBuilder;

    protected $_filterBuilder;

    protected $_sortOrderBuilder;

    protected $_filterGroupBuilder;

    protected $_stockItemRepository;

    protected $_SearchInterface;

    protected $_fullText;


    /**
     * Magento constructor.
     * @param CatalogSearch $catalogSearchHelper
     * @param FilterableAttributeList $filterableAttributeList
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(CatalogSearch $catalogSearchHelper, FilterableAttributeList $filterableAttributeList, ProductRepositoryInterface $productRepository, SearchCriteriaBuilder $searchCriteriaBuilder, FilterBuilder $filterBuilder, FilterGroupBuilder $filterGroupBuilder, StockItemRepository $stockItemRepository, SearchInterface $SearchInterface, SortOrder $sortOrder, Fulltext $fulltext)
    {

        $this->_catalogSearchHelper = $catalogSearchHelper;
        $this->_filterableAttributeList = $filterableAttributeList;
        $this->_productRepository = $productRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_stockItemRepository = $stockItemRepository;
        $this->_SearchInterface = $SearchInterface;
        $this->_sortOrderBuilder = $sortOrder;
        $this->_fullText = $fulltext;
    }

    /**
     * Devuelve el nombre del motor de busqueda
     * @return string
     */
    public function getEngineName()
    {
        return "Magento";
    }

    /**
     * Devuelve si es valido el criterio de busqueda
     * @param $searchTerm
     * @param $storeId
     * @return bool
     */
    public function isValidSearchData($searchTerm, $storeId)
    {

        if (is_null($searchTerm) || strlen($searchTerm) == 0) {
            return false;
        }
        $helper = $this->_catalogSearchHelper;
        $len = strlen($searchTerm);
        if ($len < $helper->getMinQueryLength() || $len > $helper->getMaxQueryLength()) {
            return false;
        }
        return true;

    }

    /**
     * @param $storeId
     * @param $searchTerm
     * @param $searchOrder
     * @param $searchDir
     * @param $page
     * @param $pageSize
     * @param $totalSearchResults
     * @param $attrs_applied
     * @param $attrs_available
     * @return array
     */
    public function search($storeId, $searchTerm, $searchOrder, $searchDir, $page, $pageSize, &$totalSearchResults, &$attrs_applied, &$attrs_available)
    {

        $search_criteria = $this->_searchCriteriaBuilder->create();

        $search_criteria->setRequestName("quick_search_container");

        $filter = $this->_filterBuilder->setField('search_term')
            ->setValue($searchTerm)
            ->setConditionType("like")
            ->create();

        $filterGroup = $this->_filterGroupBuilder->addFilter($filter)->create();

        $search_criteria->setFilterGroups([$filterGroup]);

        $resultx = $this->_SearchInterface->search($search_criteria);

        $totalSearchResults = $resultx->getTotalCount();

        $products = $resultx->getItems();

        $result = array();
        
        foreach ($products as $product) {

            $id = $product->getId();
            $score = $product->getCustomAttribute("score");
            $result[$id] = $score->getValue();

        }


        $product_ids = array_keys($result);


        if($searchOrder == "name" || $searchOrder == "price") {


            $searchDirection = ($searchDir == "desc") ? SortOrder::SORT_DESC : SortOrder::SORT_ASC;
            $this->_searchCriteriaBuilder->addSortOrder($searchOrder, $searchDirection);

            $searchCriteria2 = $this->_searchCriteriaBuilder->addFilter(new Filter([
                Filter::KEY_FIELD => 'entity_id',
                Filter::KEY_CONDITION_TYPE => 'in',
                Filter::KEY_VALUE => $product_ids
            ]))->create();

            $searchCriteria2->setCurrentPage($page)->setPageSize($pageSize);

            $products = $this->_productRepository->getList($searchCriteria2)->getItems();

            $products_return = array();

            foreach ($products as $product){

                $products_return[] = $product->getId();
            }

            return $products_return;

        } else {


            //Aqui deberia ordenadar result
            /*
            if ($searchDir == "desc") {

                asort($result);
            } else {
                arsort($result);
            }*/


            $result = array_slice($product_ids, $pageSize * ($page - 1), $pageSize);
            return $result;

        }

    }

}