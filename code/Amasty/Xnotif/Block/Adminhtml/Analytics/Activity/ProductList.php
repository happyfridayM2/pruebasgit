<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Block\Adminhtml\Analytics\Activity;

use Magento\Backend\Block\Template;
use Amasty\Xnotif\Model\ResourceModel\Stock\Subscription\CollectionFactory as StockCollectionFactory;
use Amasty\Xnotif\Model\ResourceModel\Stock\Subscription\Collection as StockCollection;

/**
 * Class ProductList
 */
class ProductList extends Template
{
    protected $_template = 'Amasty_Xnotif::analytics/activity.phtml';

    /**
     * @var StockCollectionFactory
     */
    private $stockCollectionFactory;

    public function __construct(
        StockCollectionFactory $stockCollectionFactory,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->stockCollectionFactory = $stockCollectionFactory;
    }

    /**
     * @param int $limit
     *
     * @return StockCollection
     */
    public function getLastSubscribers($limit = 10)
    {
        $stockCollection = $this->stockCollectionFactory->create();

        $stockCollection->getSelect()
            ->order('add_date DESC')
            ->limit($limit);

        return $stockCollection;
    }

    /**
     * @return string
     */
    public function getMoreUrl()
    {
        return $this->getUrl('xnotif/subscription/index');
    }
}
