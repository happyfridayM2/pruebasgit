<?php
namespace Magebees\Products\Block\Adminhtml\Import\Edit\Tab;

class Importlog extends \Magento\Backend\Block\Widget\Grid\Extended
{
	protected $_importlogFactory;
	protected $registry = null;
	protected $_flag=false;
	protected $_count=0;
	protected $_request;
	
	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
		\Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Data\FormFactory $formFactory,
		\Magebees\Products\Model\ImportlogFactory $importlogFactory,
        array $data = array()
    ) {
		$this->registry = $registry;
		$this->_request = $request; 
		$this->_importlogFactory = $importlogFactory;
	   parent::__construct($context, $backendHelper, $data);
	}
	protected function _construct()	{
		parent::_construct();
		$this->setId('productGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}
	protected function _prepareCollection()	{
		$collection = $this->_importlogFactory->create()->getCollection();
		$collection->getSelect()->group('product_sku'); // For Distinct
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	protected function _prepareColumns() 
	{	
		 $this->addColumn(
            'log_id',
            [
                'header' => __('Log ID'),
				'width'     => 5,
				'align'     => 'right',
				'sortable'  => true,
				'index'     => 'log_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
		$this->addColumn(
            'product_sku',
            [
                'header' => __('Product SKU'),
				'width'     => 5,
				'align'     => 'right',
				'sortable'  => true,
				'index'     => 'product_sku',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
		$this->addColumn(
            'error_information',
            [
                'header' => __('Error'),
				'width'     => 5,
				'align'     => 'right',
				'sortable'  => true,
				'index'     => 'error_information',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
		$this->addColumn(
            'error_type',
            [
                'header' => __('Error Level'),
				'width'     => 5,
				'align'     => 'right',
				'sortable'  => true,
				'index'     => 'error_type',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
				'type'      => 'options',
				'options'   => array('0'=>'Minor','1'=>'Major'),
				'frame_callback' => array($this, 'decorateStatus')
            ]
        );
        return parent::_prepareColumns();
	}
	public function decorateStatus($value, $row, $column, $isExport)
    {
            if ($value=='Major') {
                $cell = '<span class="grid-severity-critical"><span>'.$value.'</span></span>';
            } else {
                $cell = '<span class="grid-severity-minor"><span>'.$value.'</span></span>';
            }
			return $cell;
    }
	
	public function getGridUrl()
    {
        return $this->getUrl('*/import/importgrid', array('_current' => true));
    }
	
	public function getMainButtonsHtml()
    {
        $html = '';
        if($this->getFilterVisibility()){
            $html.= $this->getResetFilterButtonHtml();
            $html.= $this->getSearchButtonHtml();
        }
        return $html;
    }
	public function getImportButtonHtml()
    {
        return $this->getChildHtml('import_button');
    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}