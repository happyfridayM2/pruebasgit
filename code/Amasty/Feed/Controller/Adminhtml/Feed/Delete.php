<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Amasty\Feed\Api\FeedRepositoryInterface;
use Amasty\Feed\Controller\Adminhtml\AbstractFeed;
use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 *
 * @package Amasty\Feed
 */
class Delete extends AbstractFeed
{
    /**
     * @var FeedRepositoryInterface
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        FeedRepositoryInterface $repository,
        LoggerInterface $logger,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($feedId = $this->getRequest()->getParam('id')) {
            try {
                $this->repository->deleteById($feedId);
                $this->messageManager->addSuccessMessage(__('You deleted the feed.'));

                return $this->_redirect('amfeed/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete the feed right now. Please review the log and try again.')
                );
                $this->logger->critical($e);

                return $this->_redirect('amfeed/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a feed to delete.'));

        return $this->_redirect('amfeed/*/');
    }
}
