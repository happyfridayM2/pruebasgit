<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Model\ResourceModel;

use Amasty\ShopbyPage\Api\Data\PageInterface;
use Amasty\ShopbyPage\Api\PageRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PageRepository
 *
 * @package Amasty\ShopbyPage\Model\ResourceModel
 */
class PageRepository implements PageRepositoryInterface
{
    /**
     * @var \Amasty\ShopbyPage\Model\ResourceModel\Page
     */
    protected $pageResourceModel;

    /**
     * @var \Amasty\ShopbyPage\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Amasty\ShopbyPage\Api\Data\PageInterfaceFactory
     */
    protected $pageDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Amasty\ShopbyPage\Api\Data\PageSearchResultsInterfaceFactory
     */
    protected $pageSearchResultsFactory;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    public function __construct(
        \Amasty\ShopbyPage\Model\ResourceModel\Page $pageResourceModel,
        \Amasty\ShopbyPage\Model\PageFactory $pageFactory,
        \Amasty\ShopbyPage\Api\Data\PageSearchResultsInterfaceFactory $pageSearchResultsFactory,
        \Amasty\ShopbyPage\Api\Data\PageInterfaceFactory $pageDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Amasty\Base\Model\Serializer $serializer
    ) {
        $this->pageResourceModel = $pageResourceModel;
        $this->pageFactory = $pageFactory;
        $this->pageSearchResultsFactory = $pageSearchResultsFactory;
        $this->pageDataFactory = $pageDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->serializer = $serializer;
    }

    /**
     * @param PageInterface $pageData
     *
     * @return PageInterface
     */
    public function save(PageInterface $pageData)
    {
        $outputData = $this->dataObjectProcessor
            ->buildOutputDataArray($pageData, PageInterface::class);

        $this->normalizeOutputData($outputData);

        $page = $this->pageFactory->create()
            ->setData($outputData);

        $this->pageResourceModel
            ->save($page)
            ->saveStores($page);

        return $this->get($page->getId());
    }

    /**
     * @param $data
     * @param $key
     * @param string $delimiter
     */
    protected function implodeMultipleData(&$data, $key, $delimiter = ',')
    {
        if (array_key_exists($key, $data) && is_array($data[$key])) {
            $data[$key] = implode($delimiter, $data[$key]);
        } else {
            $data[$key] = null;
        }
    }

    /**
     * @param $data
     * @param $key
     */
    protected function serializeMultipleData(&$data, $key)
    {
        if (array_key_exists($key, $data)) {
            $data[$key] = $this->serializer->serialize($data[$key]);
        } else {
            $data[$key] = null;
        }
    }

    /**
     * @param $data
     */
    protected function normalizeOutputData(&$data)
    {
        if (array_key_exists('top_block_id', $data) && $data['top_block_id'] === '') {
            $data['top_block_id'] = null;
        }

        if (array_key_exists('bottom_block_id', $data) && $data['bottom_block_id'] === '') {
            $data['bottom_block_id'] = null;
        }

        $this->implodeMultipleData($data, 'categories');
        $this->serializeMultipleData($data, 'conditions');
    }

    /**
     * @param $data
     */
    protected function normalizeInputData(&$data)
    {
        if (array_key_exists('categories', $data)) {
            $this->processCategoryField($data['categories']);
        }

        if (array_key_exists('store_id', $data)) {
            $data['stores'] = $data['store_id'];
        }

        if (array_key_exists('conditions', $data)) {
            $this->processConditionsField($data['conditions']);
        }
    }

    /**
     * @param string $categories
     */
    private function processCategoryField(&$categories)
    {
        if ($categories !== '') {
            $categories = explode(',', $categories);
        }
    }

    /**
     * @param string $conditions
     */
    private function processConditionsField(&$conditions)
    {
        if ($conditions !== ''
            && ($conditionsArr = $this->serializer->unserialize($conditions))
            && is_array($conditionsArr)
        ) {
            array_walk(
                $conditionsArr,
                function (&$condition) {
                    if (is_string($condition)) {
                        try {
                            $condition = $this->serializer->unserialize($condition);
                        } catch (\Exception $e) {
                            $condition = [];
                        }
                    }
                }
            );
            $conditions = $conditionsArr;
        } else {
            $conditions = [];
        }
    }

    /**
     * @param int $id
     *
     * @return PageInterface
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        $page = $this->pageFactory->create();
        $this->pageResourceModel->load($page, $id);

        if (!$page->getId()) {
            throw new NoSuchEntityException(__('Page with id "%1" does not exist.', $id));
        }

        return $this->getPageData($page);
    }

    /**
     * @param \Amasty\ShopbyPage\Model\Page $page
     *
     * @return PageInterface
     */
    protected function getPageData(\Amasty\ShopbyPage\Model\Page $page)
    {
        $pageData = $this->pageDataFactory->create();
        $inputData = $page->getData();

        $this->normalizeInputData($inputData);

        $this->dataObjectHelper->populateWithArray(
            $pageData,
            $inputData,
            PageInterface::class
        );

        return $pageData;
    }

    /**
     * @param int $categoryId
     * @param int $storeId
     *
     * @return \Amasty\ShopbyPage\Api\Data\PageSearchResultsInterface
     */
    public function getList($categoryId, $storeId)
    {
        $searchResults = $this->pageSearchResultsFactory->create();

        $collection = $this->pageFactory->create()->getCollection()
            ->addFieldToFilter(
                'categories',
                [
                    ['finset' => $categoryId],
                    ['eq' => 0],
                    ['null' => true]
                ]
            )
            ->addStoreFilter($storeId);

        $pagesData = [];

        /** @var \Amasty\ShopbyPage\Model\Page $page */
        foreach ($collection as $page) {
            $pagesData[] = $this->getPageData($page);
        }

        usort(
            $pagesData,
            function (PageInterface $a, PageInterface $b) {
                return count($b->getConditions()) - count($a->getConditions());
            }
        );

        $searchResults->setTotalCount($collection->getSize());

        return $searchResults->setItems($pagesData);
    }

    /**
     * @param PageInterface $pageData
     *
     * @return bool true on success
     */
    public function delete(PageInterface $pageData)
    {
        return $this->deleteById($pageData->getPageId());
    }

    /**
     * @param int $id
     *
     * @return bool true on success
     */
    public function deleteById($id)
    {
        $page = $this->pageFactory->create();
        $this->pageResourceModel->load($page, $id);
        $this->pageResourceModel->delete($page);

        return true;
    }
}
