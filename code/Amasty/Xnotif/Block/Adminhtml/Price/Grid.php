<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Block\Adminhtml\Price;

/**
 * Class Grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Amasty\Xnotif\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Amasty\Xnotif\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->collectionFactory = $collectionFactory;
    }

    public function _construct()
    {
        parent::_construct();
        $this->setId('priceGrid');
        $this->setDefaultSort('cnt');
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
        $collection->joinPriceTable();

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

        $this->addColumn('name', [
            'header' => __('Name'),
            'index' => 'name',
        ]);

        $this->addColumn('sku', [
            'header' => __('SKU'),
            'index' => 'sku',
        ]);

        $this->addColumn('cnt', [
            'header' => __('Count'),
            'index' => 'cnt',
            'filter' => false,
        ]);

        $this->addColumn('first_d', [
            'header' => __('First Subscription'),
            'index' => 'first_d',
            'type' => 'datetime',
            'width' => '150px',
            'gmtoffset' => true,
            'default' => ' ---- ',
            'filter' => false,
        ]);

        $this->addColumn('last_d', [
            'header' => __('Last Subscription'),
            'index' => 'last_d',
            'type' => 'datetime',
            'width' => '150px',
            'gmtoffset' => true,
            'default' => ' ---- ',
            'filter' => false,
        ]);

        parent::_prepareColumns();
        return $this;
    }

    /**
     * @param $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('catalog/product/edit', ['id' => $row->getProductId()]);
    }
}
