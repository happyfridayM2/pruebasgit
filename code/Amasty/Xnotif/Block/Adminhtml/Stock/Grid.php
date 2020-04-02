<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Block\Adminhtml\Stock;

/**
 * Class Grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Amasty\Xnotif\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\Xnotif\Helper\Config
     */
    private $config;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Amasty\Xnotif\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Amasty\Xnotif\Helper\Config $config,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );

        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
    }

    public function _construct()
    {
        parent::_construct();
        $this->setId('stockGrid');
        $this->setDefaultSort('cnt');
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function addColors($value)
    {
        return
            '<div style="
                width: 50px;
                margin: 0 auto;
                border-radius: 3px;
                text-align: center;
                background-color: ' . $this->getBackgroundColor($value) . '
            ">' .
                $value .
            '</div>';
    }

    /**
     * @param int $value
     *
     * @return string
     */
    protected function getBackgroundColor($value)
    {
        switch ($value) {
            case 0:
                $color = "green";
                break;
            case 1:
                $color = "lightcoral";
                break;
            case 2:
                $color = "indianred";
                break;
            case 3:
                $color = "brown";
                break;
            case 4:
                $color = "firebrick";
                break;
            case 5:
                $color = "darkred";
                break;
            default:
                $color = "red";
        }

        return $color;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            'catalog/product/edit',
            ['id' => $row->getProductId()]
        );
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->getProductCollection();

        $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
        $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
        if ($this->getColumn($columnId) && $this->getColumn($columnId)->getIndex()) {
            $dir = strtolower($dir) == 'desc' ? 'desc' : 'asc';
            $this->getColumn($columnId)->setDir($dir);
            $collection->getSelect()->order($columnId . ' ' . $dir);
        }

        $collection->setIsCustomerMode(true);
        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return \Amasty\Xnotif\Model\ResourceModel\Product\Collection
     */
    protected function getProductCollection()
    {
        $collection = $this->collectionFactory->create();
        $collection->joinStockTable();

        return $collection;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'website',
                [
                    'header' => __('Websites'),
                    'width' => '100px',
                    'sortable' => false,
                    'index' => 'website_id',
                    'renderer' => \Amasty\Xnotif\Block\Adminhtml\Stock\Renderer\Website::class,
                    'filter' => false,
                ]
            );
        }

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
            ]
        );

        $this->addColumn(
            'first_d',
            [
                'header' => __('First Subscription'),
                'index' => 'first_d',
                'type' => 'datetime',
                'width' => '150px',
                'gmtoffset' => true,
                'default' => ' ---- ',
                'filter' => false,
            ]
        );
        $this->addColumn(
            'last_d',
            [
                'header' => __('Last Subscription'),
                'index' => 'last_d',
                'type' => 'datetime',
                'width' => '150px',
                'gmtoffset' => true,
                'default' => ' ---- ',
                'filter' => false,
            ]
        );

        $this->addColumn(
            'total_cnt',
            [
                'header' => __('Total Number of Subscriptions'),
                'index' => 'total_cnt',
                'filter' => false,
                'align' => 'center',
                'width' => '150px'
            ]
        );

        $this->addColumn(
            'cnt',
            [
                'header' => __('Customers Awaiting Notification'),
                'index' => 'cnt',
                'filter' => false,
                'frame_callback' => [$this, 'addColors'],
                'width' => '150px'
            ]
        );

        $this->addExportType('*/*/exportAlertsCsv', __('CSV'));
        $this->addExportType('*/*/exportAlertsXml', __('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Prepare grid massaction actions
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $email = $this->getTestNotificationEmail();
        if ($email) {
            $this->setMassactionIdField('entity_id');

            $this->getMassactionBlock()->addItem(
                'delete',
                [
                    'label'   => __('Test Notification'),
                    'url'     => $this->getUrl('xnotif/*/test'),
                    'confirm' => __(
                        'Test notification will be sent to the following email address: %1',
                        $email
                    ),
                ]
            );
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getTestNotificationEmail()
    {
        return $this->config->getTestEmail();
    }
}
