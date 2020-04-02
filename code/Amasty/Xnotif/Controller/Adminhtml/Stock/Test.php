<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Adminhtml\Stock;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Test
 */
class Test extends \Magento\Backend\App\Action
{
    /**
     * @var \Amasty\Xnotif\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\Xnotif\Model\Observer
     */
    private $observer;

    public function __construct(
        Action\Context $context,
        \Amasty\Xnotif\Model\Observer $observer,
        \Amasty\Xnotif\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->observer = $observer;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $count = 0;
        try {
            $collection = $this->getCollection();

            if ($collection->getSize()) {
                foreach ($collection as $alert) {
                    $this->observer->sendTestNotification($alert);
                    $count++;
                }
            } else {
                $this->messageManager->addErrorMessage(
                    __('Please select the item(s).')
                );
            }
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        if ($count) {
            $this->messageManager->addSuccessMessage(__('Test notification has been successfully sent.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }

    /**
     * @return \Amasty\Xnotif\Model\ResourceModel\Product\Collection
     */
    protected function getCollection()
    {
        $selected = $this->getRequest()->getParam('massaction', []);
        return $this->collectionFactory->create()->joinStockTable()
            ->addFieldToFilter('entity_id', $selected);
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
