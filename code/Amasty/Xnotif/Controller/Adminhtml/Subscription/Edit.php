<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\ProductAlert\Model\StockFactory;

/**
 * Class Edit
 */
class Edit extends Action
{
    /**
     * @var StockFactory
     */
    private $stockFactory;

    public function __construct(
        Context $context,
        StockFactory $stockFactory
    ) {
        parent::__construct($context);
        $this->stockFactory = $stockFactory;
    }

    /**
     * Edit action
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $model = $this->stockFactory->create();

        if ($id) {
            try {
                $model->load($id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This subscription no longer exists.'));
                return $this->_redirect('*/*/edit', ['id' => $id]);
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Xnotif::xnotif_stock_subscription');
        $resultPage->addBreadcrumb(__('Notifications'), __('Out of Stock Subscriptions'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Subscription') : __('New Subscription')
        );

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Xnotif::subscription');
    }
}
