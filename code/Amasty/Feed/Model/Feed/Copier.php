<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Feed;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Model\Config\Source\FeedStatus;

/**
 * Class Copier
 *
 * @package Amasty\Feed
 */
class Copier
{
    /**
     * @var \Amasty\Feed\Model\FeedRepository
     */
    private $feedRepository;

    public function __construct(
        \Amasty\Feed\Model\FeedRepository $feedRepository
    ) {
        $this->feedRepository = $feedRepository;
    }

    private function duplicate(FeedInterface $feed)
    {
        $duplicate = $this->feedRepository->getEmptyModel();
        $duplicate->setData($feed->getData());
        $duplicate->setIsDuplicate(true);
        $duplicate->setOriginalId($feed->getId());

        $duplicate->setExecuteMode('manual');
        $duplicate->setStatus(FeedStatus::NOT_GENERATED);
        $duplicate->setGeneratedAt(null);
        $duplicate->setId(null);
        return $duplicate;
    }

    public function copy(FeedInterface $feed)
    {
        $duplicate = $this->duplicate($feed);

        $duplicate->setName($duplicate->getName() . '-duplicate');
        $duplicate->setFilename($duplicate->getFilename() . '-duplicate');

        return $this->feedRepository->save($duplicate, true);
    }

    /**
     * Create a new feed template based on this feed
     *
     * @param FeedInterface $feed
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function template(FeedInterface $feed)
    {
        $duplicate = $this->duplicate($feed);

        $duplicate->setIsTemplate(true);
        $duplicate->setStoreId(null);

        return $this->feedRepository->save($duplicate, true);
    }

    public function fromTemplate(FeedInterface $template, $storeId)
    {
        $duplicate = $this->duplicate($template);

        $duplicate->setIsTemplate(false);
        $duplicate->setStoreId($storeId);

        return $this->feedRepository->save($duplicate, true);
    }
}
