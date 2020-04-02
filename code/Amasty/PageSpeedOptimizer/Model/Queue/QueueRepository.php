<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Queue;

use Amasty\PageSpeedOptimizer\Api\QueueRepositoryInterface;

class QueueRepository implements QueueRepositoryInterface
{
    /**
     * @var ResourceModel\Queue
     */
    private $queueResource;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Model\Queue\ResourceModel\Queue $queueResource,
        \Amasty\PageSpeedOptimizer\Model\Queue\ResourceModel\CollectionFactory $collectionFactory
    ) {
        $this->queueResource = $queueResource;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function addToQueue(\Amasty\PageSpeedOptimizer\Api\Data\QueueInterface $queue)
    {
        $this->queueResource->save($queue);

        return $queue;
    }

    /**
     * @inheritdoc
     */
    public function removeFromQueue(\Amasty\PageSpeedOptimizer\Api\Data\QueueInterface $queue)
    {
        try {
            $this->queueResource->delete((int)$queue->getQueueId());
        } catch (\Exception $e) {
            null;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function clearQueue()
    {
        $this->queueResource->clear();
    }

    /**
     * @inheritdoc
     */
    public function shuffleQueues($limit = 10)
    {
        /** @var ResourceModel\Collection $queueCollection */
        $queueCollection = $this->collectionFactory->create();
        $queueCollection->setPageSize((int)$limit);

        $items = $queueCollection->getItems();
        /** @var \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface $queue */
        $ids = [];
        foreach ($items as $queue) {
            $ids[] = $queue->getQueueId();
        }
        if (!empty($ids)) {
            $this->queueResource->deleteByIds($ids);
        }

        return $items;
    }

    /**
     * @inheritdoc
     */
    public function isQueueEmpty()
    {
        return !(bool)$this->collectionFactory->create()->getSize();
    }

    /**
     * @inheritdoc
     */
    public function getQueueSize()
    {
        return $this->collectionFactory->create()->getSize();
    }
}
