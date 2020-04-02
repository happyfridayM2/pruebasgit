<?php

namespace Abbacus\Catalog\Block\Product\ProductList;

use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;
use Magento\Framework\Registry;



class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    protected $_catalogConfig;
	protected $_catalogSession;
	protected $_toolbarModel;
    protected $_productListHelper;
    protected $urlEncoder;
	protected $_postDataHelper;
	protected $_registry;
	public $_gridVarName = 'grid';
	protected $_gridsize = '3';
	
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\Config $catalogConfig,
        ToolbarModel $toolbarModel,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Catalog\Helper\Product\ProductList $productListHelper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
		Registry $registry,
        array $data = []
    ) {
        $this->_catalogSession = $catalogSession;
        $this->_catalogConfig = $catalogConfig;
        $this->_toolbarModel = $toolbarModel;
        $this->urlEncoder = $urlEncoder;
        $this->_productListHelper = $productListHelper;
        $this->_postDataHelper = $postDataHelper;

        $this->_registry=$registry;
        parent::__construct($context, $catalogSession, $catalogConfig, $toolbarModel, $urlEncoder, $productListHelper, $postDataHelper);
    }

    public function getAvailableGrid()
    {
		//die('d');
		$gridlimit=array('2'=>'2','3'=>'3','4'=>'4');
		return $gridlimit;
    }
	
	 public function isGridCurrent($limit)
    {
		//echo $limit.'hello'.$this->getGridSize();
        return $limit == $this->getGridSize();
    }
	
	 public function getGridSize()
    {
		 if ($page = (int) $this->getRequest()->getParam($this->getGridVarName())) {
            return $page;
        }else{
			/*if($this->_gridsize==""){
				$this->_gridsize=3;
			}*/
		  return $this->_gridsize;
		}
		/*if(Mage::registry('gridsize')){
			
		  return Mage::registry('gridsize');
		}else{
		  
		  return $this->_gridsize;
		}*/
        
    }
	
	public function getGridVarName()
    {
		//die('dsds');
		//echo $this->_gridVarName;
        return $this->_gridVarName;
    }
	
	 public function getGridtUrl($limit)
    {
		if($this->_registry->registry('gridsize')){
			$this->_registry->unregister('gridsize');
			$this->_registry->register('gridsize',$limit);
		}else{
			$this->_registry->register('gridsize',$limit);

		}
		
		//$this->_gridsize=$limit;
		return $this->getPagerUrl(array(
            $this->getGridVarName()=> $limit,
            $this->getPageVarName() => null
        ));
    }
}
