<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml;

abstract class AbstractAction extends \Magento\Backend\App\Action
{
    public $title = "";
    public $breadcrumbFirst = "";
    public $breadcrumbSecond = "";
    public $menu = "";
    public $model = "";
    public $errorDoesntExist = "";
    public $successDelete = "";
    public $msgModify = "";
    public $msgNew = "";
    public $registryName = "";
    public $helperData = null;
    protected $_resultPageFactory = null;
    protected $_resultForwardFactory = null;
    protected $_coreHelper = null;
    protected $_coreRegistry = null;
    protected $_profilesModel = null;
    protected $_variablesCollection = null;
    protected $_resource = null;
    protected $_resultRawFactory = null;
    protected $_orderRepository = null;
    protected $_orderFactory = null;
    protected $_orderItem = null;

    /**
     * AbstractAction constructor.
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
        \Magento\Sales\Model\Order\Item $orderItem
    )
    {
        $this->_resource = $resource;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_resultRawFactory = $resultRawFactory;
        $this->_coreHelper = $coreHelper;
        $this->helperData = $helperData;
        $this->_coreRegistry = $coreRegistry;
        $this->_profilesModel = $profilesModel;
        $this->_variablesCollection = $variablesCollection;
        $this->_orderRepository = $orderRepository;
        $this->_orderFactory = $orderFactory;
        $this->_orderItem = $orderItem;
        parent::__construct($context);
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_OrdersExportTool::' . $this->menu);
    }

    public function delete()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create($this->model);
                $model->setId($id);
                $model->delete();
                $this->messageManager->addSuccess(__($this->successDelete));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__($this->errorDoesntExist));
        }

        $return = $this->resultRedirectFactory->create()->setPath('ordersexporttool/' . $this->menu . '/index');
        return $return;
    }

    /**
     * Execute Edit action
     * @return mixed
     */
    public function edit()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Sales::sales");
        $resultPage->addBreadcrumb(__('Orders Export Tool'), __($this->breadcrumbFirst));
        $resultPage->addBreadcrumb(__($this->breadcrumbSecond), __($this->breadcrumbSecond));

        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create($this->model);

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__($this->errorDoesntExist));
                return $this->resultRedirectFactory->create()->setPath('ordersexporttool/' . $this->menu . '/index');
            }
        }

        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? (__($this->msgModify)) : __($this->msgNew));

        $this->_coreRegistry->register($this->registryName, $model);

        return $resultPage;
    }

    /**
     * Execute new action
     */
    public function newAction()
    {
        return $this->_resultForwardFactory->create()->forward("edit");
    }

    /**
     * Execute index action
     */
    public function index()
    {
        $this->_coreHelper->checkHeartbeat();
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Sales::sales");
        $resultPage->getConfig()->getTitle()->prepend(__($this->title));
        $resultPage->addBreadcrumb($this->breadcrumbFirst, __($this->breadcrumbFirst));
        $resultPage->addBreadcrumb($this->breadcrumbSecond, __($this->breadcrumbSecond));

        return $resultPage;
    }

    protected function _validatePostData($data)
    {
        $errorNo = true;
        if (!empty($data['layout_update_xml']) || !empty($data['custom_layout_update_xml'])) {
            $validatorCustomLayout = $this->_objectManager->create('Magento\Core\Model\Layout\Update\Validator');
            if (!empty($data['layout_update_xml']) && !$validatorCustomLayout->isValid($data['layout_update_xml'])) {
                $errorNo = false;
            }

            if (!empty($data['custom_layout_update_xml']) && !$validatorCustomLayout->isValid(
                $data['custom_layout_update_xml']
            )
            ) {
                $errorNo = false;
            }

            foreach ($validatorCustomLayout->getMessages() as $message) {
                $this->messageManager->addError($message);
            }
        }

        return $errorNo;
    }

    abstract public function execute();
}