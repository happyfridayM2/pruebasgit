<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class FromTemplate
 *
 * @package Amasty\Feed
 */
class FromTemplate extends \Amasty\Feed\Controller\Adminhtml\AbstractFeed
{
    /**
     * @var \Amasty\Feed\Model\Feed\Copier
     */
    private $feedCopier;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\Feed\Api\FeedRepositoryInterface
     */
    private $repository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\Feed\Copier $feedCopier,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Feed\Api\FeedRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->feedCopier = $feedCopier;
        $this->storeManager = $storeManager;
        $this->repository = $repository;
    }

    public function execute()
    {
        if ($feedId = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->repository->getById($feedId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This feed no longer exists.'));

                return $this->_redirect('amfeed/*');
            }

            try {
                $storeId = $this->storeManager->getStore()->getId();
                /** @var \Amasty\Feed\Model\Feed $newModel */
                $newModel = $this->feedCopier->fromTemplate($model, $storeId);

                $this->messageManager->addSuccessMessage(__('Feed %1 created', $newModel->getName()));

                return $this->_redirect('amfeed/*/edit', ['id' => $newModel->getId()]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while export feed data. Please review the error log.')
                );

                $this->logger->critical($e);
            }
        }

        return $this->_redirect('amfeed/*');
    }
}
