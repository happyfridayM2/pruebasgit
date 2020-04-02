<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Schedule\ResourceModel;

use Amasty\Feed\Api\Data\ScheduleInterface;
use Amasty\Feed\Model\CronProvider;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * Define model and resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Amasty\Feed\Model\Schedule\Schedule::class,
            \Amasty\Feed\Model\Schedule\ResourceModel\Schedule::class
        );
        parent::_construct();
    }

    /**
     * @param int $feedId
     * @param int $now
     * @param int $currentDay
     *
     * @return Collection
     */
    public function addValidateTimeFilter($feedId, $now, $currentDay)
    {
        return $this->addFieldToFilter(ScheduleInterface::FEED_ID, $feedId)
            ->addFieldToFilter(ScheduleInterface::CRON_DAY, $currentDay)
            ->addFieldToFilter(ScheduleInterface::CRON_TIME, ['lteq' => $now])
            ->addFieldToFilter(ScheduleInterface::CRON_TIME, ['gt' => $now - CronProvider::MINUTES_IN_STEP]);
    }
}
