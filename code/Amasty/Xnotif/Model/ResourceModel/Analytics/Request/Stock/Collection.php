<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model\ResourceModel\Analytics\Request\Stock;

use Amasty\Xnotif\Model\Analytics\Request\Stock;
use Amasty\Xnotif\Model\ResourceModel\Analytics\Request\Stock as StockResource;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amasty\Xnotif\Api\Analytics\Data\StockInterface;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Stock::class, StockResource::class);
    }

    /**
     * @return $this
     */
    public function groupByMonth()
    {
        $this->prepareSum();
        $this->getSelect()
            ->group('MONTH(`' . StockInterface::DATE . '`)')
            ->columns(StockInterface::DATE);

        return $this;
    }

    private function prepareSum()
    {
        $this->getSelect()
            ->reset(Select::COLUMNS)
            ->columns(
                array_map(
                    function ($field) {
                        return 'SUM(`' . $field . '`) as ' . $field;
                    },
                    [StockInterface::SUBSCRIBED, StockInterface::SENT, StockInterface::ORDERS]
                )
            );
    }

    /**
     * @return array
     */
    public function getTotalRow()
    {
        $this->prepareSum();

        return $this->getConnection()->fetchRow($this->getSelect());
    }
}
