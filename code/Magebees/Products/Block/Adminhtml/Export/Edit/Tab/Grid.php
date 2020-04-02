<?php
namespace Magebees\Products\Block\Adminhtml\Export\Edit\Tab;
use \Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date;
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
	protected $_exportfileFactory;
	public function __construct(
			\Magento\Backend\Block\Template\Context $context,
			\Magento\Backend\Helper\Data $backendHelper,
			\Magebees\Products\Model\ExportfileFactory $exportfileFactory,
			array $data = array()
	) {
		$this->_exportfileFactory = $exportfileFactory;
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
		$collection = $this->_exportfileFactory->create()->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	protected function _prepareMassaction()	{
		$this->setMassactionIdField('export_id');
		$this->getMassactionBlock()->setFormFieldName('export_id');
		$this->getMassactionBlock()->addItem(
				'delete',
				array(
						'label' => __('Delete'),
						'url' => $this->getUrl('products/export/massdelete'),
						'confirm' => __('Are you sure?'),
						'selected'=>true
				)
		);
		return $this;
	}
	protected function _prepareColumns() {
		 $this->addColumn(
            'export_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'export_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );	
 		$this->addColumn(
 				'file_name',
 				[
 						'header' => __('File Name'),
 						'index' => 'file_name',
 						'header_css_class' => 'col-name',
 						'column_css_class' => 'col-name'
 				]
 		);
		$this->addColumn(
			'exported_file_date_times',
			[
				'header' => __('Exported Date'),
				'type' => 'datetime',
				'index' => 'exported_file_date_times',
			]
		);
		$this->addColumn(
			'action',
			[
				'header' => __('Action'),
				'type' => 'action',
				'getter' => 'getId',
				'actions' => [
					[
						'caption' => __('Download'),
						'url' => ['base' => '*/*/downloadexportedfile'],
						'field' => 'id',
					],
				],
				'filter' => false,
				'sortable' => false,
				'index' => 'stores',
				'header_css_class' => 'col-action',
				'column_css_class' => 'col-action',
			]
		);
		return parent::_prepareColumns();
	}
	public function getGridUrl() {
		return $this->getUrl('*/export/grid', ['_current' => true]);
	}
	public function getRowUrl($row) {
		
	}
}