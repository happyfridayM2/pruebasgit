<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Category;

use Amasty\Feed\Controller\Adminhtml\AbstractCategory;
use Amasty\Feed\Model\Category\CategoryFactory;
use Amasty\Feed\Model\Category\Repository;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

/**
 * Class Edit
 *
 * @package Amasty\Feed
 */
class Edit extends AbstractCategory
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    public function __construct(
        Action\Context $context,
        Registry $registry,
        Repository $repository,
        CategoryFactory $categoryFactory
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->repository = $repository;
        $this->categoryFactory = $categoryFactory;
    }

    public function execute()
    {
        $model = $this->categoryFactory->create();
        if ($categoryId = $this->getRequest()->getParam('feed_category_id')) {
            try {
                $model = $this->repository->getById($categoryId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This category no longer exists.'));

                return $this->_redirect('amfeed/*');
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());

                return $this->_redirect('amfeed/*');
            }
        }

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->registry->register('current_amfeed_category', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Feed::feed_category');
        $resultPage->addBreadcrumb(__('Amasty Feed'), __('Amasty Feed'));
        $resultPage->addBreadcrumb(__('Categories Mapping Edit'), __('Categories Mapping Edit'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getFeedCategoryId() ? $model->getName() : __('New Categories Mapping')
        );

        return $resultPage;
    }
}
