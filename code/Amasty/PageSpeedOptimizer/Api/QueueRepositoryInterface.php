<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Api;

interface QueueRepositoryInterface
{
    /**
     * @param \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface $queue
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface
     */
    public function addToQueue(\Amasty\PageSpeedOptimizer\Api\Data\QueueInterface $queue);

    /**
     * @param \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface $queue
     *
     * @return bool
     */
    public function removeFromQueue(\Amasty\PageSpeedOptimizer\Api\Data\QueueInterface $queue);

    /**
     * @param int $limit
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface[]
     */
    public function shuffleQueues($limit = 10);

    /**
     * @return void
     */
    public function clearQueue();

    /**
     * @return bool
     */
    public function isQueueEmpty();

    /**
     * @return int
     */
    public function getQueueSize();
}
