<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Cron;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Api\Data\ValidProductsInterface;
use Amasty\Feed\Api\FeedRepositoryInterface;
use Amasty\Feed\Exceptions\ReindexInProgressException;
use Amasty\Feed\Model\Config\Source\Events;
use Amasty\Feed\Model\Config\Source\ExecuteModeList;
use Amasty\Feed\Model\Config\Source\FeedStatus;
use Amasty\Feed\Model\CronProvider;
use Amasty\Feed\Model\EmailManagement;
use Amasty\Feed\Model\Feed;
use Amasty\Feed\Model\FeedExport;
use Amasty\Feed\Model\JobManager;
use Amasty\Feed\Model\JobManagerFactory as JobManagerFactory;
use Amasty\Feed\Model\ValidProduct\ResourceModel\Collection as ValidProductsCollection;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class RefreshData
 *
 * Refresh feed data by cron
 */
class RefreshData
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory
     */
    private $feedCollectionFactory;

    /**
     * @var \Amasty\Feed\Model\Config
     */
    private $config;

    /**
     * @var \Amasty\Feed\Model\ValidProduct\ResourceModel\CollectionFactory
     */
    private $validProductsFactory;

    /**
     * @var EmailManagement
     */
    private $emailManagement;

    /**
     * @var \Amasty\Feed\Model\Schedule\Management
     */
    private $scheduleManagement;

    /**
     * @var \Amasty\Feed\Model\Schedule\ResourceModel\CollectionFactory
     */
    private $scheduleCollectionFactory;

    /**
     * @var \Amasty\Feed\Model\Indexer\Feed\IndexBuilder
     */
    private $indexBuilder;

    /**
     * @var JobManagerFactory
     */
    private $jobManagerFactory;

    /**
     * @var \Amasty\Feed\Model\FeedExportFactory
     */
    private $feedExportFactory;

    /**
     * @var FeedRepositoryInterface
     */
    private $feedRepository;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory $feedCollectionFactory,
        \Amasty\Feed\Model\Config $config,
        EmailManagement $emailManagement,
        \Amasty\Feed\Model\ValidProduct\ResourceModel\CollectionFactory $validProductsFactory,
        \Amasty\Feed\Model\Schedule\Management $scheduleManagement,
        \Amasty\Feed\Model\Schedule\ResourceModel\CollectionFactory $scheduleCollectionFactory,
        \Amasty\Feed\Model\Indexer\Feed\IndexBuilder $indexBuilder,
        JobManagerFactory $jobManagerFactory,
        FeedRepositoryInterface $feedRepository,
        \Amasty\Feed\Model\FeedExportFactory $feedExportFactory
    ) {
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
        $this->logger = $logger;
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->config = $config;
        $this->validProductsFactory = $validProductsFactory;
        $this->emailManagement = $emailManagement;
        $this->scheduleManagement = $scheduleManagement;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->indexBuilder = $indexBuilder;
        $this->feedExportFactory = $feedExportFactory;
        $this->jobManagerFactory = $jobManagerFactory;
        $this->feedRepository = $feedRepository;
    }

    public function execute()
    {
        $itemsPerPage = (int)$this->config->getItemsPerPage();
        /** @var \Amasty\Feed\Model\ResourceModel\Feed\Collection $collection */
        $collection = $this->feedCollectionFactory->create();
        $collection->addFieldToFilter(FeedInterface::IS_ACTIVE, 1)
            ->addFieldToFilter(FeedInterface::EXECUTE_MODE, ExecuteModeList::CRON);

        $events = $this->config->getSelectedEvents();
        $events = explode(",", $events);

        try {
            $this->indexBuilder->lockReindex();
        } catch (ReindexInProgressException $e) {
            try {
                foreach ($collection as $feed) {
                    if ($this->onSchedule($feed)) {
                        $feed->setStatus(FeedStatus::GENERATE_NEXT_CRON);
                    }
                }
                $collection->save();
            } catch (\Exception $e) {
                return;
            }

            return;
        }

        $maxJobs = $this->config->getMaxJobsCount();
        $multiProcessMode = $maxJobs > 1;

        if ($multiProcessMode) {
            /** @var JobManager $jobManager */
            $jobManager = $this->jobManagerFactory->create(['maxJobs' => $maxJobs]);
        }

        /** @var FeedExport $feedExport */
        $feedExport = $this->feedExportFactory->create([
            'multiProcessMode' => $multiProcessMode
        ]);

        /** @var Feed $feed */
        foreach ($collection as $feed) {
            try {
                if (!$this->onSchedule($feed)) {
                    continue;
                }

                $page = 1;
                $lastPage = false;
                $generationTime = date('Y-m-d H:i:s');

                /** @var ValidProductsCollection $vProductsCollection */
                $vProductsCollection = $this->validProductsFactory->create()
                    ->setPageSize($itemsPerPage)->setCurPage($page);
                $vProductsCollection->addFieldToFilter(ValidProductsInterface::FEED_ID, $feed->getId());

                $feed->setGenerationType(ExecuteModeList::CRON_GENERATED);
                $feed->setProductsAmount(0);

                while ($page <= $vProductsCollection->getLastPageNumber()) {
                    if ($page == $vProductsCollection->getLastPageNumber()) {
                        $lastPage = true;
                    }

                    $collectionData = $vProductsCollection->getData();
                    $productIds = [];

                    foreach ($collectionData as $datum) {
                        $productIds[] = $datum[ValidProductsInterface::VALID_PRODUCT_ID];
                    }

                    if ($productIds === []) {
                        throw new LocalizedException(__('There are no products to generate feed. Please check'
                            . ' Amasty Feed indexers status or feed conditions.'));
                    }

                    if ($multiProcessMode) {
                        $jobManager->waitForFreeSlot();

                        if (0 === $jobManager->fork()) { // Child process
                            $feedExport->export($feed, $page - 1, $productIds, $lastPage);

                            return;
                        }
                    } else {
                        $feedExport->export($feed, $page - 1, $productIds, $lastPage, false, $generationTime);
                    }

                    $vProductsCollection->setCurPage(++$page)->resetData();
                }

                if ($multiProcessMode) {
                    $jobManager->waitForAllJobs();
                    $feedExport->combineChunks($feed);
                    $feed->setProductsAmount($vProductsCollection->getSize());
                    $feed->setStatus(FeedStatus::READY);
                    $this->feedRepository->save($feed);
                }

                if ($events && in_array(Events::SUCCESS, $events)) {
                    $emailTemplate = $this->config->getSuccessEmailTemplate();
                    $this->emailManagement->sendEmail($feed, $emailTemplate);
                }
            } catch (\Exception $e) {
                if ($events && in_array(Events::UNSUCCESS, $events)) {
                    $emailTemplate = $this->config->getUnsuccessEmailTemplate();
                    $this->emailManagement->sendEmail($feed, $emailTemplate, $e->getMessage());
                }

                $feed->setStatus(FeedStatus::FAILED);

                $this->logger->critical($e);
            }
        }

        $collection->save();
        $this->indexBuilder->unlockReindex();
    }

    /**
     * @param Feed $feed
     *
     * @return bool
     */
    private function validateTime($feed)
    {
        $mageTime = $this->localeDate->scopeTimeStamp();
        $now = (date("H", $mageTime) * 60) + date("i", $mageTime);

        /** @var \Amasty\Feed\Model\Schedule\ResourceModel\Collection $scheduleCollection */
        $scheduleCollection = $this->scheduleCollectionFactory->create();
        $scheduleCollection->addValidateTimeFilter($feed->getId(), $now, date('w'));

        return (bool)$scheduleCollection->getSize();
    }

    /**
     * @param Feed $feed
     *
     * @return bool
     */
    private function onSchedule($feed)
    {
        $currentDateTime = $this->dateTime->gmtDate();
        $lastValidDate = date(
            'Y:m:d H:i:s',
            strtotime($currentDateTime . '-' . CronProvider::MINUTES_IN_STEP . ' minutes')
        );

        return ($lastValidDate >= $feed->getGeneratedAt()
            && ($feed->getStatus() == FeedStatus::GENERATE_NEXT_CRON || $this->validateTime($feed)));
    }
}
