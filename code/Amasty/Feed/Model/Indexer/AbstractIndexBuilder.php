<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Indexer;

use Amasty\Feed\Model\Feed;
use Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory as FeedCollectionFactory;

abstract class AbstractIndexBuilder
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var FeedCollectionFactory
     */
    private $feedCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Indexer\Model\Indexer\StateFactory
     */
    protected $stateFactory;

    /**
     * @var \Amasty\Feed\Model\Rule\GetValidFeedProducts
     */
    private $getValidFeedProducts;

    public function __construct(
        FeedCollectionFactory $feedCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\Rule\GetValidFeedProducts $getValidFeedProducts,
        \Magento\Indexer\Model\Indexer\StateFactory $stateFactory
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->logger = $logger;
        $this->stateFactory = $stateFactory;
        $this->getValidFeedProducts = $getValidFeedProducts;
    }

    /**
     * Full reindex
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexFull()
    {
        try {
            $this->doReindexFull();
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @param \Amasty\Feed\Model\Feed $feed
     * @param array $ids
     */
    public function processFeed(\Amasty\Feed\Model\Feed $feed, array $ids = [])
    {
        $result = $this->getValidFeedProducts->execute($feed, $ids);
    }

    /**
     * Full reindex Template method
     *
     * @return void
     */
    abstract protected function doReindexFull();

    /**
     * @param \Exception $exception
     *
     * @return void
     */
    protected function critical($exception)
    {
        $this->logger->critical($exception);
    }

    /**
     * Get active rules
     *
     * @return \Amasty\Feed\Model\ResourceModel\Feed\Collection
     */
    protected function getActiveFeeds()
    {
        return $this->feedCollectionFactory->create()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('is_template', 0);
    }

    /**
     * Get active rules
     *
     * @return \Amasty\Feed\Model\ResourceModel\Feed\Collection
     */
    protected function getAllFeeds()
    {
        return $this->feedCollectionFactory->create()
            ->addFieldToFilter('is_template', 0);
    }

    /**
     * Clean by feed ids
     *
     * @param array $feedIds
     *
     * @return void
     */
    protected function deleteByFeedIds($feedIds)
    {
        $this->connection->delete(
            $this->resource->getTableName('amasty_feed_valid_products'),
            ['feed_id IN (?)' => $feedIds]
        );
    }

    protected function truncateTable()
    {
        $this->connection->truncateTable($this->resource->getTableName('amasty_feed_valid_products'));
    }

    /**
     * Clean by product ids
     *
     * @param array $productIds
     *
     * @return void
     */
    protected function deleteByProductIds($productIds)
    {
        $this->connection->delete(
            $this->resource->getTableName('amasty_feed_valid_products'),
            ['valid_product_id IN (?)' => $productIds]
        );
    }
}
