<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Block\Adminhtml\Analytics\Wanted;

use Magento\Backend\Block\Template;
use Amasty\Xnotif\Model\ResourceModel\Stock\Subscription\CollectionFactory as StockCollectionFactory;
use Amasty\Xnotif\Model\ResourceModel\Stock\Subscription\Collection as StockCollection;

/**
 * Class ProductList
 */
class ProductList extends Template
{
    protected $_template = 'Amasty_Xnotif::analytics/wanted.phtml';

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
    public function getWantedProducts($limit = 5)
    {
        return $this->stockCollectionFactory->create()
            ->applyMostWanted($limit);
    }

    /**
     * @return string
     */
    public function getMoreUrl()
    {
        return $this->getUrl('xnotif/stock/index');
    }
}
