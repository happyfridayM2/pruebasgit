<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Adminhtml\Stock;

use Magento\Backend\App\Action;

/**
 * Class Delete
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\ProductAlert\Model\StockFactory
     */
    private $stockFactory;

    public function __construct(
        Action\Context $context,
        \Magento\ProductAlert\Model\StockFactory $stockFactory
    ) {
        parent::__construct($context);
        $this->stockFactory = $stockFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $alertId = (int)$this->getRequest()->getParam('alert_stock_id');

        if (!$alertId) {
            $this->messageManager->addErrorMessage(
                __(
                    'An error occurred while deleting the item from Subscriptions.'
                )
            );
        } else {
            $alert = $this->stockFactory->create()->load($alertId);
            if ($alert && $alert->getId()) {
                try {
                    $alert->delete();
                    $this->messageManager->addSuccessMessage(
                        __('The item has been deleted from Subscriptions.')
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'An error occurred while deleting the item from Subscriptions.'
                        )
                    );
                }
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Amasty_Xnotif::stock'
        );
    }
}
