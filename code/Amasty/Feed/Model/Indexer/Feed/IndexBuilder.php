<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Indexer\Feed;

use Amasty\Feed\Exceptions\ReindexInProgressException;
use Amasty\Feed\Model\Indexer\Product\ProductFeedProcessor;
use Magento\Framework\Indexer\StateInterface;

class IndexBuilder extends \Amasty\Feed\Model\Indexer\AbstractIndexBuilder
{
    /**
     * @var string
     */
    private $productIndexState;

    /**
     * @var string
     */
    private $ruleIndexState;

    /**
     * Reindex by id
     *
     * @param int $feedId
     *
     * @return void
     * @api
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reindexByFeedId($feedId)
    {
        $this->reindexByFeedIds([$feedId]);
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexByFeedIds(array $ids)
    {
        try {
            $this->doReindexByFeedIds($ids);
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    public function lockReindex()
    {
        $productIndexerState = $this->stateFactory->create()
            ->loadByIndexer(ProductFeedProcessor::INDEXER_ID);
        $this->productIndexState = $productIndexerState->getStatus();
        $ruleIndexerState = $this->stateFactory->create()
            ->loadByIndexer(FeedRuleProcessor::INDEXER_ID);
        $this->ruleIndexState = $ruleIndexerState->getStatus();

        if (in_array(StateInterface::STATUS_WORKING, [$this->productIndexState, $this->ruleIndexState])) {
            throw new ReindexInProgressException();
        }

        $productIndexerState->setStatus(StateInterface::STATUS_WORKING)->save();
        $ruleIndexerState->setStatus(StateInterface::STATUS_WORKING)->save();
    }

    public function unlockReindex()
    {
        $this->stateFactory->create()
            ->loadByIndexer(ProductFeedProcessor::INDEXER_ID)
            ->setStatus($this->productIndexState)
            ->save();

        $this->stateFactory->create()
            ->loadByIndexer(FeedRuleProcessor::INDEXER_ID)
            ->setStatus($this->ruleIndexState)
            ->save();
    }

    /**
     * Reindex by ids. Template method
     *
     * @param array $ids
     *
     * @return void
     * @throws \Exception
     */
    protected function doReindexByFeedIds($ids)
    {
        $this->deleteByFeedIds($ids);
        /** @var \Amasty\Feed\Model\ResourceModel\Feed\Collection $collection */
        $collection = $this->getAllFeeds()->addFieldToFilter('entity_id', ['in' => $ids]);

        /** @var \Amasty\Feed\Model\Feed $feed */
        foreach ($collection->getItems() as $feed) {
            $this->processFeed($feed);
        }
    }

    /**
     * Full reindex Template method
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function doReindexFull()
    {
        $this->truncateTable();

        /** @var \Amasty\Feed\Model\Feed $feed */
        foreach ($this->getAllFeeds() as $feed) {
            $this->processFeed($feed);
        }
    }
}
