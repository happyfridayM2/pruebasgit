<?php
namespace Magebees\Products\Block\Adminhtml\Import\Edit\Tab;

class Validationlog extends \Magento\Backend\Block\Widget\Grid\Extended
{
	protected $request;
	protected $_validationlogFactory;
	protected $_flag=false;
	protected $_count=0;

	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
		\Magento\Framework\App\Request\Http $request,
		\Magebees\Products\Model\ValidationlogFactory $validationlogFactory,
        array $data = array()
    ) {
		$this->request = $request;
		$this->_validationlogFactory = $validationlogFactory;
		parent::__construct($context, $backendHelper, $data); //working
	}
	protected function _construct()	{
		parent::_construct();
		$this->setId('productImportGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}
	protected function _prepareCollection()	{
		$collection = $this->_validationlogFactory->create()->getCollection();
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
		return $this->getUrl('*/import/validgrid', ['_current' => true]);
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
	protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
	
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}