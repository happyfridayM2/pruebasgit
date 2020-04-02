<?php
/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Profiles;
/**
 * Class Ftp
 * @package Wyomind\OdersExportTool\Controller\Adminhtml\Profiles
 */
class Ftp extends AbstractProfiles
{

    /**
     * @var \Wyomind\OrdersExportTool\Helper\Ftp
     */
    protected $_ftpHelper;

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
        \Wyomind\OrdersExportTool\Helper\Ftp $emailHelper
    )
    {
        parent::__construct($context, $resource, $resultPageFactory, $resultForwardFactory, $resultRawFactory, $coreHelper, $helperData, $coreRegistry, $profilesModel, $variablesCollection, $orderRepository, $orderFactory, $orderItem);
        $this->_ftpHelper=$emailHelper;
    }


    /**
     *
     */
    public function execute()
    {


        try {
            $data=$this->getRequest()->getParams();
            $ftp=$this->_ftpHelper->getConnection($data);

            $content=__("Connection succeeded");
            $ftp->close();
        } catch (\Exception $e) {
            $content=$e->getMessage();
        }

        $this->getResponse()->representJson($this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode($content));
    }

}
