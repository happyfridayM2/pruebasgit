<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model\Repository\Analytics\Daily;

use Amasty\Xnotif\Api\Analytics\Data\Daily\StockInterface;
use Amasty\Xnotif\Api\Analytics\Daily\StockRepositoryInterface;
use Amasty\Xnotif\Model\Analytics\Request\Daily\StockFactory;
use Amasty\Xnotif\Model\ResourceModel\Analytics\Request\Daily\Stock as StockResource;
use Amasty\Xnotif\Model\ResourceModel\Analytics\Request\Daily\Stock\CollectionFactory;
use Amasty\Xnotif\Model\ResourceModel\Analytics\Request\Daily\Stock\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * Class StockRepository
 */
class StockRepository implements StockRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var StockFactory
     */
    private $stockFactory;

    /**
     * @var StockResource
     */
    private $stockResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $stocks;

    /**
     * @var CollectionFactory
     */
    private $stockCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        StockFactory $stockFactory,
        StockResource $stockResource,
        CollectionFactory $stockCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->stockFactory = $stockFactory;
        $this->stockResource = $stockResource;
        $this->stockCollectionFactory = $stockCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(StockInterface $stock)
    {
        try {
            if ($stock->getId()) {
                $stock = $this->getById($stock->getId())->addData($stock->getData());
            }
            $this->stockResource->save($stock);
            unset($this->stocks[$stock->getId()]);
        } catch (\Exception $e) {
            if ($stock->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save stock with ID %1. Error: %2',
                        [$stock->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new stock. Error: %1', $e->getMessage()));
        }

        return $stock;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->stocks[$id])) {
            /** @var \Amasty\Xnotif\Model\Analytics\Request\Daily\Stock $stock */
            $stock = $this->stockFactory->create();
            $this->stockResource->load($stock, $id);
            if (!$stock->getId()) {
                throw new NoSuchEntityException(__('Stock with specified ID "%1" not found.', $id));
            }
            $this->stocks[$id] = $stock;
        }

        return $this->stocks[$id];
    }

    /**
     * @inheritdoc
     */
    public function delete(StockInterface $stock)
    {
        try {
            $this->stockResource->delete($stock);
            unset($this->stocks[$stock->getId()]);
        } catch (\Exception $e) {
            if ($stock->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove stock with ID %1. Error: %2',
                        [$stock->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove stock. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $stockModel = $this->getById($id);
        $this->delete($stockModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Xnotif\Model\ResourceModel\Analytics\Request\Daily\Stock\Collection $stockCollection */
        $stockCollection = $this->stockCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $stockCollection);
        }
        $searchResults->setTotalCount($stockCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $stockCollection);
        }
        $stockCollection->setCurPage($searchCriteria->getCurrentPage());
        $stockCollection->setPageSize($searchCriteria->getPageSize());
        $stocks = [];
        /** @var StockInterface $stock */
        foreach ($stockCollection->getItems() as $stock) {
            $stocks[] = $this->getById($stock->getId());
        }
        $searchResults->setItems($stocks);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $stockCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $stockCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $stockCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     * @param $sortOrders
     * @param Collection $stockCollection
     */
    private function addOrderToCollection($sortOrders, Collection $stockCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $stockCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }
}
