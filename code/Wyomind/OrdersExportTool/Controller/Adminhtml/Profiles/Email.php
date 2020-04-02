<?php
/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Profiles;
/**
 * Class Ftp
 * @package Wyomind\OdersExportTool\Controller\Adminhtml\Profiles
 */
class Email extends AbstractProfiles
{

    /**
     * @var \Wyomind\OrdersExportTool\Helper\Email
     */
    protected $_emailHelper;


    /**
     * Email constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Wyomind\Core\Helper\Data $coreHelper
     * @param \Wyomind\OrdersExportTool\Helper\Data $helperData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Wyomind\OrdersExportTool\Model\Profiles $profilesModel
     * @param \Wyomind\OrdersExportTool\Model\ResourceModel\Variables\Collection $variablesCollection
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param \Wyomind\OrdersExportTool\Helper\Email $emailHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Wyomind\Core\Helper\Data $coreHelper,
        \Wyomind\OrdersExportTool\Helper\Data $helperData,
        \Magento\Framework\Registry $coreRegistry,
        \Wyomind\OrdersExportTool\Model\Profiles $profilesModel,
        \Wyomind\OrdersExportTool\Model\ResourceModel\Variables\Collection $variablesCollection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory,
        \Magento\Sales\Model\Order\Item $orderItem,
        \Wyomind\OrdersExportTool\Helper\Email $emailHelper

    )
    {

        parent::__construct($context, $resource, $resultPageFactory, $resultForwardFactory, $resultRawFactory, $coreHelper, $helperData, $coreRegistry, $profilesModel, $variablesCollection, $orderRepository, $orderFactory, $orderItem);
        $this->_emailHelper=$emailHelper;

    }


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {

        try {
            $data=$this->getRequest()->getParams();
            $this->_emailHelper->sendEmail($data);
            $content=__("Email sent");

        } catch (\Exception $e) {
            $content=$e->getMessage();
        }

        $this->getResponse()->representJson($this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode($content));
    }

}
