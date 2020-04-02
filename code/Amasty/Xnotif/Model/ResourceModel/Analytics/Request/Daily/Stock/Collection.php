<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model\ResourceModel\Analytics\Request\Daily\Stock;

use Amasty\Xnotif\Model\Analytics\Request\Daily\Stock;
use Amasty\Xnotif\Model\ResourceModel\Analytics\Request\Daily\Stock as StockResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Stock::class, StockResource::class);
    }
}
