<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Category;

use Amasty\Feed\Controller\Adminhtml\AbstractCategory;
use Amasty\Feed\Model\Category\Repository;
use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 *
 * @package Amasty\Feed
 */
class Delete extends AbstractCategory
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Repository
     */
    private $repository;

    public function __construct(
        Repository $repository,
        LoggerInterface $logger,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->repository = $repository;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($categoryId = $this->getRequest()->getParam('id')) {
            try {
                $this->repository->deleteById($categoryId);
                $this->messageManager->addSuccessMessage(__('You deleted the category.'));

                return $this->_redirect('amfeed/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete the category right now. Please review the log and try again.')
                );
                $this->logger->critical($e);

                return $this->_redirect('amfeed/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a category to delete.'));
        return $this->_redirect('amfeed/*/');
    }
}
