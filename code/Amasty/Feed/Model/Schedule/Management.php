<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Schedule;

use Amasty\Feed\Api\Data\ScheduleInterface;
use Amasty\Feed\Api\ScheduleRepositoryInterface;
use Amasty\Feed\Model\Schedule\ResourceModel\CollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Management
 */
class Management extends AbstractModel
{
    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;

    /**
     * @var ScheduleRepositoryInterface
     */
    private $scheduleRepository;

    /**
     * @var CollectionFactory
     */
    private $scheduleCollectionFactory;

    public function __construct(
        ScheduleFactory $scheduleFactory,
        ScheduleRepositoryInterface $scheduleRepository,
        CollectionFactory $scheduleCollectionFactory,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->scheduleRepository = $scheduleRepository;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param int $feedId
     * @param array $data
     */
    public function saveScheduleData($feedId, $data)
    {
        $this->removeExistData($feedId);

        if (!empty($data[ScheduleInterface::CRON_DAY]) && !empty($data[ScheduleInterface::CRON_TIME])) {
            foreach ($data[ScheduleInterface::CRON_DAY] as $cronDay) {
                foreach ($data[ScheduleInterface::CRON_TIME] as $cronTime) {
                    /** @var \Amasty\Feed\Model\Schedule\Schedule $schedule */
                    $schedule = $this->scheduleFactory->create();
                    $schedule->setFeedId($feedId)
                        ->setCronDay($cronDay)
                        ->setCronTime($cronTime);
                    $this->scheduleRepository->save($schedule);
                }
            }
        }
    }

    /**
     * @param \Amasty\Feed\Model\Feed $feed
     *
     * @return \Amasty\Feed\Model\Feed
     */
    public function prepareScheduleData($feed)
    {
        $cronDay = [];
        $cronTime = [];
        /** @var \Amasty\Feed\Model\Schedule\ResourceModel\Collection $scheduleCollection */
        $scheduleCollection = $this->scheduleCollectionFactory->create();
        $scheduleCollection->addFieldToFilter(ScheduleInterface::FEED_ID, $feed->getId());

        /** @var \Amasty\Feed\Model\Schedule\Schedule $schedule */
        foreach ($scheduleCollection->getItems() as $schedule) {
            $cronDay[] = $schedule->getCronDay();
            $cronTime[] = $schedule->getCronTime();
        }
        $feed['cron_day'] = $cronDay;
        $feed['cron_time'] = $cronTime;

        return $feed;
    }

    /**
     * @param int $feedId
     */
    private function removeExistData($feedId)
    {
        $this->scheduleRepository->deleteByFeedId($feedId);
    }
}
