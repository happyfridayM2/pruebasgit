<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Setup\Operation;

use Amasty\Xnotif\Model\ResourceModel\Stock\Subscription\CollectionFactory as StockCollectionFactory;
use Amasty\Xnotif\Model\ResourceModel\Stock\Subscription\Collection;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Amasty\Xnotif\Api\Analytics\Data\StockInterface as Stock;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class AnalyticsData
 */
class AnalyticsData
{
    /**
     * @var StockCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var null|AdapterInterface
     */
    private $connection = null;

    public function __construct(StockCollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $this->connection = $setup->getConnection();

        $stockSubscriptions = $this->collectionFactory->create();

        $statistics = array_merge_recursive(
            $this->getCountByDate(clone $stockSubscriptions->getSelect(), 'add_date', 'subscribed'),
            $this->getCountByDate(clone $stockSubscriptions->getSelect(), 'send_date', 'sent'),
            $this->getOrders($stockSubscriptions)
        );

        foreach ($statistics as &$statistic) {
            if (is_array($statistic['date'])) {
                $statistic['date'] = $statistic['date'][0];
            }
            if (!isset($statistic['subscribed'])) {
                $statistic['subscribed'] = 0;
            }
            if (!isset($statistic['sent'])) {
                $statistic['sent'] = 0;
            }
            if (!isset($statistic['orders'])) {
                $statistic['orders'] = 0;
            }
        }

        $this->connection->insertMultiple(
            $setup->getTable(Stock::MAIN_TABLE),
            $statistics
        );
    }

    /**
     * @param Select $select
     * @param string $fieldDate
     * @param string $alias
     *
     * @return array
     */
    private function getCountByDate(Select $select, $fieldDate, $alias)
    {
        $select
            ->reset(Select::COLUMNS)
            ->columns('count(*) as ' . $alias)
            ->columns('DATE(`' . $fieldDate . '`) as date')
            ->group('date')
            ->having('`date` IS NOT NULL');
        $result = $this->connection->fetchAll($select);

        return $this->updateResult($result);
    }

    /**
     * @param Collection $stockSubscriptions
     *
     * @return array
     */
    private function getOrders($stockSubscriptions)
    {
        $stockSubscriptions
            ->_renderFiltersBefore()
            ->joinSales(false)
            ->getSelect()
            ->reset(Select::COLUMNS)
            ->columns('DATE(sales.created_at) as date')
            ->columns('SUM(sales_item.base_row_total) as orders')
            ->group('date');
        $result = $this->connection->fetchAll($stockSubscriptions->getSelect());

        return $this->updateResult($result);
    }

    /**
     * @param array $result
     *
     * @return array
     */
    private function updateResult(&$result)
    {
        foreach ($result as $key => $data) {
            if (isset($data['date'])) {
                $result[$data['date']] = $result[$key];
                unset($result[$key]);
            }
        }

        return $result;
    }
}
