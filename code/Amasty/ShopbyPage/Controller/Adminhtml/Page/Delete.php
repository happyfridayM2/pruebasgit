<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Controller\Adminhtml\Page;

use \Magento\Backend\App\Action;
use Amasty\ShopbyPage\Api\Data\PageInterfaceFactory;
use Amasty\ShopbyPage\Api\PageRepositoryInterface;

/**
 * Class Delete
 *
 * @package Amasty\ShopbyPage\Controller\Adminhtml\Page
 */
class Delete extends Action
{
    /**
     * @var PageInterfaceFactory
     */
    protected $pageDataFactory;

    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    public function __construct(
        Action\Context $context,
        PageInterfaceFactory $pageDataFactory,
        PageRepositoryInterface $pageRepository
    ) {
        $this->pageDataFactory = $pageDataFactory;
        $this->pageRepository = $pageRepository;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShopbyPage::page');
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->pageRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The page has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a page to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
