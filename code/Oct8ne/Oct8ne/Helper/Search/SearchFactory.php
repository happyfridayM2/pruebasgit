<?php
/**
 * Created by PhpStorm.
 * User: migue
 * Date: 20/06/2017
 * Time: 19:20
 */

namespace Oct8ne\Oct8ne\Helper\Search;


use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;

/**
 * Devuelve una instancia de search para el motor de busqueda configurado
 * Class SearchFactory
 * @package Oct8ne\Oct8ne\Helper\Search
 */
class SearchFactory extends AbstractHelper
{


    /**
     * @var Magento
     */
    protected $_magento;

    /**
     * @var Solr
     */
    protected $_solr;

    /**
     * @var Sli
     */
    protected $_sli;

    /**
     * @var Celebros
     */
    protected $_celebros;


    /**
     * SearchFactory constructor.
     * @param Context $context
     * @param Magento $magento
     * @param Sli $sli
     * @param Solr $solr
     * @param Celebros $celebros
     */
    public function __construct(Context $context, Magento $magento, Sli $sli, Solr $solr, Celebros $celebros)
    {

        $this->_celebros = $celebros;
        $this->_solr = $solr;
        $this->_sli = $sli;
        $this->_magento = $magento;

        parent::__construct($context);
    }


    /**
     *
     * @param $engine
     * @return Celebros|Magento|Sli|Solr
     */
    public function getInstance($engine)
    {

        if ($engine == "Magento") {

            return $this->_magento;

        } else if ($engine == "Celebros") {

            return $this->_celebros;

        } else if ($engine == "Solr") {

            return $this->_solr;

        } else if ($engine == "Sli") {

            return $this->_sli;
        } else {

            return $this->_magento;
        }
    }
}