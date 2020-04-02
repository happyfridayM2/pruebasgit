<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles;

/**
 * Prepare the profiles grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManager $_storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManager $_storeManager,
        array $data=[]
    )
    {
        $this->_collectionFactory=$collectionFactory;
        $this->_storeManager=$_storeManager;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('ordersexporttoolGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        $collection=$this->_collectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header'=>__('ID'),
                'align'=>'right',
                'width'=>'50px',
                'index'=>'id',
                'filter'=>false
            ]
        );

        $this->addColumn(
            'name',
            [
                'header'=>__('Filename'),
                'align'=>'left',
                'index'=>'name'
            ]
        );

        $this->addColumn(
            'type',
            [
                'header'=>__('File format'),
                'align'=>'left',
                'index'=>'type',
                'type'=>'options',
                'options'=>[
                    null=>__(' '),
                    1=>'xml',
                    2=>'txt',
                    3=>'csv'
                ],
                'renderer'=>'Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Renderer\Type'
            ]
        );

        $this->addColumn(
            'link',
            [
                'header'=>__('Last generated file'),
                'align'=>'left',
                'index'=>'link',
                'type'=>'options',
                'filter'=>false,
                'sortable'=>false,
                'renderer'=>'Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Renderer\Link'
            ]
        );

        $this->addColumn(
            'last_exported_id',
            [
                'header'=>__('Starting with order #'),
                'align'=>'left',
                'index'=>'last_exported_id',
                'renderer'=>'Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Renderer\StartWith'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header'=>__('Status'),
                'index'=>'status',
                'renderer'=>'Wyomind\OrdersExportTool\Block\Adminhtml\Progress\Status',
            ]
        );
        $this->addColumn(
            'updated_at',
            [
                'header'=>__('Last update'),
                'align'=>'left',
                'index'=>'updated_at',
                'type'=>'datetime',
                'width'=>'150px'
            ]
        );

        if (!$this->_storeManager->hasSingleStore()) {
            $this->addColumn(
                'store_id',
                [
                    'header'=>__('Store View'),
                    'index'=>'store_id',
                    'type'=>'store',
                    'renderer'=>'Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Renderer\Storeviews',
                ]
            );
        }


        $this->addColumn(
            'action',
            [
                'header'=>__('Action'),
                'align'=>'left',
                'filter'=>false,
                'sortable'=>false,
                "type"=>"action",
                'getter'=>'getId',
                'index'=>'id',
                'header_css_class'=>'col-action',
                'column_css_class'=>'col-action',
                'renderer'=>'Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Renderer\Action',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     * @param \Magento\Object $row
     * @return boolean
     */
    public function getRowUrl($row)
    {
        return false;
    }
}