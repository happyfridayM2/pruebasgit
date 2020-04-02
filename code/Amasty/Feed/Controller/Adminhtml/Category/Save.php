<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Category;

use Amasty\Feed\Model\Category\CategoryFactory;
use Amasty\Feed\Model\Category\Repository;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class Save
 *
 * @package Amasty\Feed
 */
class Save extends \Amasty\Feed\Controller\Adminhtml\AbstractCategory
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Repository $repository,
        Action\Context $context,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                $model = $this->repository->getCategoryEmptyEntity();
                if ($categoryId = (int)$this->getRequest()->getParam('feed_category_id')) {
                    $model = $this->repository->getById($categoryId);
                }

                $model->addData($data);
                $this->_session->setPageData($model->getData());
                $this->repository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the category mapping.'));
                $this->_session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('amfeed/*/edit', ['feed_category_id' => $model->getId()]);
                }

                return $this->_redirect('amfeed/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if (!empty($categoryId)) {
                    return $this->_redirect('amfeed/*/edit', ['feed_category_id' => $categoryId]);
                } else {
                    return $this->_redirect('amfeed/*/new');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the feed data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->_session->setPageData($data);
                return $this->_redirect('amfeed/*/edit', ['id' => $categoryId]);
            }
        }

        return $this->_redirect('amfeed/*/');
    }
}
