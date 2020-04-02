<?php

namespace Oct8ne\Oct8ne\Helper\Search;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;


/**
 * Created by PhpStorm.
 * User: migue
 * Date: 20/06/2017
 * Time: 18:03
 */
abstract class Base extends AbstractHelper
{

    private $params = array();

    protected $_context;

    protected $_scopeconfig;

    public abstract function getEngineName();

    public abstract function isValidSearchData($searchTerm, $storeI);

    public abstract function search($storeId, $searchTerm, $searchOrder, $searchDir, $page, $pageSize, &$totalSearchResults, &$attrs_applied, &$attrs_available);


    /**
     * Base constructor.
     * @param Context $context
     */
    public function __construct(Context $context){

        parent::__construct($context);

        $this->_context = $context;
        $this->_scopeconfig = $context->getScopeConfig();

    }

    /**
     * Obtiene los valores de configuracion
     * @param $param
     * @return null|string
     */
    protected function getEngineParam($param) {
        if (isset($this->params[$param])) {
            return trim($this->params[$param]);
        }
        return NULL;
    }

    /**
     * Obtiene el request
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest() {

        return $this->_context->getRequest();
    }


    /**
     * Crea una opcion de filtado
     * @param $value
     * @param $valueLabel
     * @param $count
     * @return array
     */
    public function createFilterOption($value,$valueLabel,$count){

            return array("value" => $value, "valueLabel" => $valueLabel, "count" => $count);
    }

    /**
     * Crea un grupo de filter options (aplicados y no aplicados)
     * @param $param
     * @param $paramLabel
     * @param $options
     * @param null $currentValueLabel
     * @param null $currentValue
     * @return array
     */
    protected function createFilterInfo($param, $paramLabel, $options, $currentValueLabel = null, $currentValue = null) {
        $result = array(
            'param' => $param,
            'paramLabel' => $paramLabel,
            'options' => $options
        );

        //si es filtro aplicado
        if (!is_null($currentValue)) {
            $result['valueLabel'] = $currentValueLabel;
            $result['value'] = $currentValue;
        }
        return $result;
    }

    /**
     * Crea un filtro aplicado
     * @param $param
     * @param $paramLabel
     * @param $value
     * @param $valueLabel
     * @return array
     */
    protected function createAppliedFilter($param, $paramLabel, $value, $valueLabel) {

        return array(
            "param" => $param,
            "paramLabel" => $paramLabel,
            "value" => $value,
            "valueLabel" => $valueLabel
        );
    }

    /**
     * Obtiene los filtros aplicados
     * @return array
     */
    protected function getAppliedFilters() {
        $request = $this->getRequest();
        $queryStringParams = array_keys($this->_context->getRequest()->getParams());
        $facets = array();
        foreach ($queryStringParams as $param) {
            // Ignore standard query standard params
            if (substr($param, 0, 1) == '_' || $param=='currency' || $param=='locale' || $param == 'callback' || $param == 'search' || $param == 'store' || $param == 'orderby' || $param == 'dir' || $param == 'page' || $param == 'pageSize') {
                continue;
            }

            // If we get here, the param is a facet
            $value = $request->getParam($param);
            if (!is_null($value) && trim($value)!="") {
                $facets[$param] = $value;
            }
        }
        return $facets;
    }

    /**
     * Devuelve los filtros disponibles pero no aplicados
     * @param $attrs_applied
     * @param $attrs_available
     * @return array
     */
    protected function getAvailableButNotAppliedFilters($attrs_applied, $attrs_available) {
        $result = array();
        $applied = array();
        foreach ($attrs_applied as $attr) {
            $applied[$attr["param"]] = 1;
        }
        foreach ($attrs_available as $attr) {
            if (!isset($applied[$attr["param"]])) {
                $result[] = $attr;
            }
        }
        return $result;
    }

}