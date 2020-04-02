<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Api\Data\ValidProductsInterface;
use Amasty\Feed\Model\Config\Source\ExecuteModeList;
use Amasty\Feed\Model\Config\Source\FeedStatus;
use Amasty\Feed\Model\ValidProduct\ResourceModel\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;

class Ajax extends \Amasty\Feed\Controller\Adminhtml\AbstractFeed
{
    /**
     * @var \Magento\Framework\UrlFactory
     */
    private $urlFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var \Amasty\Feed\Api\FeedRepositoryInterface
     */
    private $feedRepository;

    /**
     * @var \Amasty\Feed\Model\Config
     */
    private $config;

    /**
     * @var \Amasty\Feed\Model\Indexer\Feed\IndexBuilder
     */
    private $indexBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\Feed\Model\FeedExport
     */
    private $feedExport;

    /**
     * @var \Amasty\Feed\Model\Filesystem\FeedOutput
     */
    private $feedOutput;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\UrlFactory $urlFactory,
        CollectionFactory $collectionFactory,
        \Amasty\Feed\Api\FeedRepositoryInterface $feedRepository,
        \Amasty\Feed\Model\Config $config,
        \Amasty\Feed\Model\Indexer\Feed\IndexBuilder $indexBuilder,
        \Amasty\Feed\Model\FeedExport $feedExport,
        \Amasty\Feed\Model\Filesystem\FeedOutput $feedOutput
    ) {
        $this->urlFactory = $urlFactory;
        $this->feedRepository = $feedRepository;
        $this->config = $config;

        parent::__construct($context);
        $this->indexBuilder = $indexBuilder;
        $this->logger = $logger;
        $this->feedExport = $feedExport;
        $this->feedOutput = $feedOutput;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Magento\Framework\UrlInterface
     */
    private function getUrlInstance()
    {
        return $this->urlFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $page = (int)$this->getRequest()->getParam('page', 0);
        $feedId = $this->getRequest()->getParam('feed_entity_id');
        $body = [];
        $feed = null;
        $currentPage = $page + 1; // Valid page for searchCriteria

        try {
            $this->indexBuilder->lockReindex();
            $itemsPerPage = (int)$this->config->getItemsPerPage();
            $lastPage = false;
            /** @var FeedInterface $feed */
            $feed = $this->feedRepository->getById($feedId);

            $feed->setGenerationType(ExecuteModeList::MANUAL_GENERATED);

            if ($page === 0) {
                $feed->setProductsAmount(0);
            }

            $validProductsCollection = $this->collectionFactory->create();
            $validProductsCollection->addFieldToFilter(ValidProductsInterface::FEED_ID, $feedId)
                ->setPageSize($itemsPerPage)
                ->setCurPage($currentPage)
                ->addFieldToSelect(ValidProductsInterface::VALID_PRODUCT_ID);
            $collectionSize = $validProductsCollection->getSize();
            $validProducts = array_map(function ($item) {
                return $item[ValidProductsInterface::VALID_PRODUCT_ID];
            }, $validProductsCollection->getData());

            $totalPages = ceil($collectionSize / $itemsPerPage);

            if ((int)$page == $totalPages - 1 || $totalPages == 0) {
                $lastPage = true;
            }

            if (count($validProducts) === 0) {
                throw new NotFoundException(__('There are no products to generate feed. Please check Amasty Feed'
                    . ' indexers status or feed conditions.'));
            }

            $this->feedExport->export($feed, $page, $validProducts, $lastPage);

            $body['exported'] = count($validProducts);
            $body['isLastPage'] = $lastPage;
            $body['total'] = $collectionSize;
        } catch (\Amasty\Feed\Exceptions\ReindexInProgressException $e) {
            $body['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e);

            $feed->setStatus(FeedStatus::FAILED);
            $this->feedRepository->save($feed);

            $body['error'] = $e->getMessage();
        }

        if (!isset($body['error'])) {
            $urlInstance = $this->getUrlInstance();

            $routeParams = [
                '_direct' => 'amfeed/feed/download',
                '_query' => [
                    'id' => $feed->getEntityId()
                ]
            ];

            $href = $urlInstance
                ->setScope($feed->getStoreId())
                ->getUrl(
                    '',
                    $routeParams
                );

            if (!empty($body['isLastPage'])) {
                $feedOutput = $this->feedOutput->get($feed);
                $body['download'] = $href . "&file=" . $feedOutput['filename'];
            }
        } else {
            $body['error'] = substr($body['error'], 0, 150) . '...';
        }
        $this->indexBuilder->unlockReindex();

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($body);

        return $resultJson;
    }
}
