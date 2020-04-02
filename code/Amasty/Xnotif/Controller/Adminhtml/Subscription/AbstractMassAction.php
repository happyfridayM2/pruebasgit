<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;
use Amasty\Xnotif\Model\ResourceModel\Stock\Subscription\CollectionFactory;
use Magento\ProductAlert\Model\StockFactory;
use Magento\ProductAlert\Model\Stock;
use Magento\ProductAlert\Model\ResourceModel\Stock as ResourceStock;
use Amasty\Xnotif\Model\ResourceModel\Stock\Subscription\Collection;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class AbstractMassAction
 */
abstract class AbstractMassAction extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StockFactory
     */
    protected $stockFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ResourceStock
     */
    protected $stockResource;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        LoggerInterface $logger,
        StockFactory $stockFactory,
        CollectionFactory $collectionFactory,
        ResourceStock $stockResource
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->logger = $logger;
        $this->stockFactory = $stockFactory;
        $this->collectionFactory = $collectionFactory;
        $this->stockResource = $stockResource;
    }

    /**
     * Execute action for subscription
     *
     * @param Stock $subscription
     */
    abstract protected function itemAction(Stock $subscription);

    /**
     * Mass action execution
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0
        /** @var Collection $collection */
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $collectionSize = $collection->getSize();
        if ($collectionSize) {
            try {
                foreach ($collection->getItems() as $subscription) {
                    $this->itemAction($subscription);
                }

                $this->messageManager->addSuccessMessage($this->getSuccessMessage($collectionSize));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($this->getErrorMessage());
                $this->logger->critical($e);
            }
        } else {
            $this->messageManager->addSuccessMessage($this->getSuccessMessage());
        }

        return $this->resultRedirectFactory->create()
            ->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getErrorMessage()
    {
        return __('We can\'t change item right now. Please review the log and try again.');
    }

    /**
     * @param int $collectionSize
     *
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($collectionSize = 0)
    {
        if ($collectionSize) {
            return __('A total of %1 record(s) have been changed.', $collectionSize);
        }

        return __('No records have been changed.');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Xnotif::subscription');
    }
}
