<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Block\Adminhtml\Report;

/**
 * Class Grid
 * @package Amasty\Xnotif\Block\Adminhtml\Report
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Amasty\Xnotif\Model\ResourceModel\Stock\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Amasty\Xnotif\Model\ResourceModel\Stock\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );

        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        /** @var \Amasty\Xnotif\Model\ResourceModel\Stock\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->joinAdditionalTables();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => __('EMAIL'),
                'index' => 'final_email',
                'filter' => false,
            ]
        );

        $this->addColumn(
            'first_d',
            [
                'header' => __('Subscription Date'),
                'index' => 'first_d',
                'type' => 'datetime',
                'width' => '150px',
                'gmtoffset' => true,
                'default' => ' ---- ',
                'filter' => false,
            ]
        );

        parent::_prepareColumns();
        return $this;
    }
}
