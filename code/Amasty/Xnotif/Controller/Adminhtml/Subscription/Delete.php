<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\ProductAlert\Model\StockFactory;
use Magento\ProductAlert\Model\ResourceModel\Stock as StockResource;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Delete
 */
class Delete extends Action
{
    /**
     * @var StockFactory
     */
    private $stockFactory;

    /**
     * @var StockResource
     */
    private $stockResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        StockFactory $stockFactory,
        StockResource $stockResource,
        LoggerInterface $logger,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->stockFactory = $stockFactory;
        $this->stockResource = $stockResource;
        $this->logger = $logger;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                $stockSubscription = $this->stockFactory->create();
                $this->stockResource->load(
                    $stockSubscription,
                    $id
                );
                $this->stockResource->delete(
                    $stockSubscription
                );

                $this->messageManager->addSuccessMessage(__('You deleted the subscription.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
            }
        }

        return $this->resultRedirectFactory->create()
            ->setUrl($this->getUrl('*/*/'));
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Xnotif::subscription');
    }
}
