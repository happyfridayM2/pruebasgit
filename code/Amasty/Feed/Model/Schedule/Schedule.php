<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Schedule;

use Amasty\Feed\Api\Data\ScheduleInterface;
use Amasty\Feed\Model\Schedule\ResourceModel\Schedule as ScheduleResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Schedule
 */
class Schedule extends AbstractModel implements ScheduleInterface
{
    /**
     * Init resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(ScheduleResourceModel::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getFeedId()
    {
        return $this->getData(self::FEED_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setFeedId($feedId)
    {
        return $this->setData(self::FEED_ID, $feedId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCronTime()
    {
        return $this->getData(self::CRON_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCronTime($cronTime)
    {
        return $this->setData(self::CRON_TIME, $cronTime);
    }

    /**
     * {@inheritdoc}
     */
    public function getCronDay()
    {
        return $this->getData(self::CRON_DAY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCronDay($cronDay)
    {
        return $this->setData(self::CRON_DAY, $cronDay);
    }
}
