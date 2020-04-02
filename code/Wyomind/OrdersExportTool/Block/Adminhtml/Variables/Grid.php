<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Variables;

/**
 * Prepare the variable grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Wyomind\OrdersExportTool\Model\ResourceModel\Variables\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context                              $context
     * @param \Magento\Backend\Helper\Data                                         $backendHelper
     * @param \Wyomind\OrdersExportTool\Model\ResourceModel\Variables\CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManager                                    $_storeManager
     * @param array                                                                $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Wyomind\OrdersExportTool\Model\ResourceModel\Variables\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManager $_storeManager,
        array $data = []
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->_storeManager = $_storeManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
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
        $collection = $this->_collectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'scope',
            [
                'header' => __('Scope'),
                'align' => 'left',
                'index' => 'scope',
                'filter' => false
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'align' => 'left',
                'index' => 'name',
                'filter' => false
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'align' => 'left',
                'filter' => false,
                'sortable' => false,
                'type' => 'action',
                'getter' => 'getId',
                'index' => 'id',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
                'actions' => [
                    [
                        'url' => ['base' => '*/*/edit'],
                        'caption' => __('Edit'),
                        'field' => 'id'
                    ],
                    [
                        'url' => ["base" => '*/*/delete'],
                        'confirm' => __('Are you sure you want to delete this profile ?'),
                        'caption' => __('Delete'),
                        'field' => 'id'
                    ]
                ]
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @param \Magento\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return false;
    }
}