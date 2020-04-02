<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Api;

/**
 * @api
 */
interface FeedRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Feed\Api\Data\FeedInterface $feed
     * @param bool $withReindex
     *
     * @return \Amasty\Feed\Api\Data\FeedInterface
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Feed\Api\Data\FeedInterface $feed, $withReindex = false);

    /**
     * Get by id
     *
     * @param int $feedId
     *
     * @return \Amasty\Feed\Api\Data\FeedInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($feedId);

    /**
     * Get model without data
     *
     * @return \Amasty\Feed\Api\Data\FeedInterface
     */
    public function getEmptyModel();

    /**
     * Delete
     *
     * @param \Amasty\Feed\Api\Data\FeedInterface $feed
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Feed\Api\Data\FeedInterface $feed);

    /**
     * Delete by id
     *
     * @param int $feedId
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($feedId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
