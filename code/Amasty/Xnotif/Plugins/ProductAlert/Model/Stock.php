<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\ProductAlert\Model;

use Magento\ProductAlert\Model\ResourceModel\Stock\Collection;
use Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory;
use Magento\ProductAlert\Model\Stock as NativeStock;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Amasty\Xnotif\Model\Analytics\Collector;

/**
 * Class Stock
 */
class Stock
{
    const SUBSCRIPTION_DATE = 'add_date';

    /**
     * @var DateTimeFactory
     */
    private $dateFactory;

    /**
     * @var Collector
     */
    private $collector;

    /**
     * @var CollectionFactory
     */
    private $stockCollectionFactory;

    public function __construct(
        DateTimeFactory $dateFactory,
        Collector $collector,
        CollectionFactory $stockCollectionFactory
    ) {
        $this->dateFactory = $dateFactory;
        $this->collector = $collector;
        $this->stockCollectionFactory = $stockCollectionFactory;
    }

    /**
     * @param NativeStock $subject
     * @param string $key
     * @param null $index
     *
     * @return array
     */
    public function beforeGetData(NativeStock $subject, $key = '', $index = null)
    {
        if ($key == self::SUBSCRIPTION_DATE) {
            $subject->setAddDate($this->dateFactory->create()->gmtDate());
        }

        return [$key, $index];
    }

    /**
     * collect subscribed customers
     *
     * @param NativeStock $subject
     *
     * @return array
     */
    public function beforeSave(NativeStock $subject)
    {
        if (!$this->checkIfSubscribed($subject)) {
            $this->collector->updateDaily(Collector::ACTION_SUBSCRIBED);
        }

        return [];
    }

    /**
     * @param NativeStock $subscription
     *
     * @return bool
     */
    private function checkIfSubscribed(NativeStock $subscription)
    {
        /** @var Collection $collection */
        $collection = $this->stockCollectionFactory->create()
            ->addWebsiteFilter($subscription->getWebsiteId())
            ->addFieldToFilter('product_id', $subscription->getProductId())
            ->addStatusFilter(0)
            ->setCustomerOrder();

        if ($subscription->getCustomerId()) {
            $collection->addFieldToFilter('customer_id', $subscription->getCustomerId());
        } else {
            $collection->addFieldToFilter('email', $subscription->getEmail());
        }

        return (bool)$collection->getSize();
    }
}
