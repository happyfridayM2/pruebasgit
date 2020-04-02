<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Queue\ResourceModel;

use Amasty\PageSpeedOptimizer\Api\Data\QueueInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Queue extends AbstractDb
{
    const TABLE_NAME = 'amasty_page_speed_optimizer_queue';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, QueueInterface::QUEUE_ID);
    }

    public function clear()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }

    public function deleteByIds($ids = [])
    {
        $this->getConnection()->delete($this->getMainTable(), [QueueInterface::QUEUE_ID . ' in (?) ' => $ids]);
    }
}
