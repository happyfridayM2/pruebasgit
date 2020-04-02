<?php
namespace  Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup;

use Magento\Store\Model\Store;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    
    protected $_menugroupcollection;
    protected $moduleManager;
    protected $_type;
    protected $_setsFactory;
    protected $_status;
    protected $_visibility;
    protected $_websiteFactory;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magebees\Navigationmenu\Model\ResourceModel\Menucreatorgroup\Collection $menugroupcollection,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
		\Magebees\Navigationmenu\Helper\Data $helper,
        array $data = []
    ) {
        
        $this->_productFactory = $productFactory;
        $this->moduleManager = $moduleManager;
        $this->_type = $type;
        $this->_menugroupcollection = $menugroupcollection;
        $this->_setsFactory = $setsFactory;
        $this->_status = $status;
        $this->_visibility = $visibility;
        $this->_websiteFactory = $websiteFactory;
		$this->_helper = $helper;
        
        parent::__construct($context, $backendHelper, $data);
    }
    
    protected function _construct()
    {
        parent::_construct();
        $this->setId('menucreatorgroupGrid');
        $this->setDefaultSort('group_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        //$this->setUseAjax(true);
    }
    
    protected function _prepareCollection()
    {
        $collection = $this->_menugroupcollection;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('group_id');
        $this->getMassactionBlock()->setFormFieldName('navigationmenu');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                        'label' => __('Delete'),
                        'url' => $this->getUrl('*/*/massdelete'),
                        'confirm' => __('Are you sure?'),
                        'selected'=>true
                ]
        );
        
        $menu_level=$this->_helper->getmassMenuLevel();
        array_unshift($menu_level, ['label'=>'', 'value'=>'']);
        $this->getMassactionBlock()->addItem(
            'level',
            [
                        'label' => __('Change Menu Level'),
                        'url' => $this->getUrl('*/*/masslevel', ['_current'=>true]),
                        'additional' => [
                        'visibility' => [
                         'name' => 'level',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => __('Menu Level'),
                         'values' => $menu_level
                                            ]
                                        ]
                ]
        );
        $status=$this->_helper->getOptionArray();
        $this->getMassactionBlock()->addItem(
            'status',
            [
                        'label' => __('Change Status'),
                        'url' =>$this->getUrl('*/*/massstatus', ['_current'=>true]),
                        'additional' => [
                        'visibility' => [
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => __('Status'),
                         'values' => $status
                                ]
                            ]
                ]
        );
        
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'group_id',
            [
                'header' => __('Group ID'),
                'align' => 'left',
                'width' => '50px',
                'index' => 'group_id'
            ]
        );
        
        $this->addColumn(
            'title',
            [
                'header' => __('Group Title'),
                'align' => 'left',
                'index' => 'title'
            ]
        );

        $this->addColumn(
            'position',
            [
                'header' => __('Align'),
                'align' => 'left',
                'width' => '80px',
                'index' => 'position',
                'type' => 'options',
                'options' =>  [
                        'horizontal' => 'Horizontal',
                        'vertical' => 'Vertical'
                ]
            ]
        );

        $this->addColumn(
            'menutype',
            [
                'header' => __('Menu Type'),
                'align' => 'left',
                'width' => '80px',
                'index' => 'menutype',
                'type' => 'options',
                'options' =>  [
                        'mega-menu' => 'Mega Menu',
                        'smart-expand' => 'Smart Expand',
                        'always-expand' => 'Always Expand',
                        'list-item' => 'List Item',
                ]
            ]
        );

        $this->addColumn(
            'level',
            [
                'header' => __('Menu Level'),
                'align' => 'left',
                'width' => '80px',
                'index' => 'level',
                'type' => 'options',
                'options' =>  [
                        '0' => 'Only Root Level',
                        '1' => 'One Level',
                        '2' => 'Second Level',
                        '3' => 'Third Level',
                        '4' => 'Fourth Level',
                        '5' => 'Fifth Level',
                ]
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'align' => 'left',
                'width' => '80px',
                'index' => 'status',
                'type' => 'options',
                'options' =>  [
                        1 => 'Enabled',
                        2 => 'Disabled'
                ]
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
               'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' =>  [
                         [
                                'caption' =>__('Edit'),
                                'url' =>  [
                                        'base' => '*/*/edit'
                                ],
                                'field' => 'id'
                         ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true
            ]
        );
        return parent::_prepareColumns();
    }
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}
