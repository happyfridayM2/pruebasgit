<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Adminhtml;

/**
 * Class Index
 */
abstract class AbstractControllerIndex extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $collectionFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function getResultPageFactory()
    {
        return $this->resultPageFactory;
    }

    protected function addMessage()
    {
        $scheduleCollection = $this->getScheduledCollection();

        if ($scheduleCollection->getSize() == 0) {
            $this->messageManager->addNoticeMessage(
                __('No cron job "amasty_xnotif_product_alert" found. Please check your cron configuration.')
            );
        }
    }

    /**
     * @return \Magento\Cron\Model\ResourceModel\Schedule\Collection
     */
    protected function getScheduledCollection()
    {
        $scheduleCollection = $this->collectionFactory->create()
            ->addFieldToFilter('job_code', ['eq' => 'amasty_xnotif_product_alert']);
        $scheduleCollection->getSelect()->order('schedule_id desc');

        return $scheduleCollection;
    }
}
