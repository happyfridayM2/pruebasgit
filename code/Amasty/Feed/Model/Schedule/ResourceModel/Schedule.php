<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Schedule\ResourceModel;

use Amasty\Feed\Api\Data\ScheduleInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Schedule
 */
class Schedule extends AbstractDb
{
    const TABLE = 'amasty_feed_schedule';

    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(self::TABLE, ScheduleInterface::ID);
    }

    /**
     * @param int $feedId
     */
    public function deleteByFeedId($feedId)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->getConnection();

        $query = $connection->deleteFromSelect(
            $connection->select()->from($this->getMainTable(), ScheduleInterface::FEED_ID)->where(
                ScheduleInterface::FEED_ID . ' IN (?)',
                $feedId
            ),
            $this->getMainTable()
        );

        $connection->query($query);
    }
}
