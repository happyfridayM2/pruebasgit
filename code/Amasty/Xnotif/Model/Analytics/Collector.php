<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model\Analytics;

use Amasty\Xnotif\Model\ResourceModel\Stock\Subscription\CollectionFactory as SubscriptionFactory;
use Amasty\Xnotif\Model\Analytics\Request\Daily\StockFactory as DailyStockFactory;
use Amasty\Xnotif\Model\Analytics\Request\Daily\Stock as DailyStock;
use Amasty\Xnotif\Api\Analytics\StockRepositoryInterface;
use Amasty\Xnotif\Api\Analytics\Daily\StockRepositoryInterface as DailyRepositoryInterface;

/**
 * Class Collector
 */
class Collector
{
    const ACTION_SUBSCRIBED = 'subscribed';

    const ACTION_SENT = 'sent';

    /**
     * @var Request\StockFactory
     */
    private $requestStockFactory;

    /**
     * @var DailyStockFactory
     */
    private $dailyStockFactory;

    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var DailyRepositoryInterface
     */
    private $dailyRepository;

    public function __construct(
        SubscriptionFactory $subscriptionFactory,
        Request\StockFactory $requestStockFactory,
        DailyStockFactory $dailyStockFactory,
        StockRepositoryInterface $stockRepository,
        DailyRepositoryInterface $dailyRepository
    ) {
        $this->requestStockFactory = $requestStockFactory;
        $this->dailyStockFactory = $dailyStockFactory;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->stockRepository = $stockRepository;
        $this->dailyRepository = $dailyRepository;
    }

    public function execute()
    {
        $this->collectStock();
    }

    public function collectStock()
    {
        /** @var DailyStock $dailyStock */
        $dailyStock = $this->dailyStockFactory->create()
            ->loadPrevious();

        if ($dailyStock->getId()) {
            $collectedStock = $this->requestStockFactory->create()
                ->setSubscribed($dailyStock->getSubscribed())
                ->setSent($dailyStock->getSent())
                ->setDate($dailyStock->getDate())
                ->setOrders($this->collectOrders($dailyStock->getDate()));

            $this->stockRepository->save($collectedStock);
        }
    }

    /**
     * @param string $action
     * @param int $increment
     */
    public function updateDaily($action, $increment = 1)
    {
        /** @var DailyStock $dailyStock */
        $dailyStock = $this->dailyStockFactory->create()
            ->loadCurrent();

        $dailyStock->setData(
            $action,
            $dailyStock->getData($action) + $increment
        );
        $dailyStock->updateDate();

        $this->dailyRepository->save($dailyStock);
    }

    /**
     * @param string $date
     *
     * @return string
     */
    private function collectOrders($date)
    {
        return $this->subscriptionFactory->create()
            ->getTotals($date);
    }
}
