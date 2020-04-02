<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Controller\Adminhtml\Page;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry as CoreRegistry;
use Amasty\ShopbyPage\Controller\RegistryConstants;
use Amasty\ShopbyPage\Api\Data\PageInterfaceFactory;
use Amasty\ShopbyPage\Api\PageRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class Edit
 *
 * @package Amasty\ShopbyPage\Controller\Adminhtml\Page
 */
class Edit extends Action
{
    /**
     * Core registry
     *
     * @var CoreRegistry
     */
    protected $_coreRegistry = null;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var PageRepositoryInterface
     */
    protected $_pageRepository;

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        CoreRegistry $registry,
        PageInterfaceFactory $pageFactory,
        PageRepositoryInterface $pageRepository,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_pageRepository = $pageRepository;
        $this->_pageFactory = $pageFactory;
        $this->dataObjectHelper = $dataObjectHelper;

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
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('page')
            ->addBreadcrumb(__('Manage Custom Pages'), __('Manage Custom Pages'));
        return $resultPage;
    }

    /**
     * Edit page
     * @inheritdoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $isExisting = (bool)$id;

        $page = $this->_pageFactory->create();
        if ($isExisting
            && !($page = $this->loadPage($id))
        ) {
            $result = $this->resultRedirectFactory->create();
            $result->setPath('amasty_shopbypage/*/index');
        } else {
            $data = $this->_session->getFormData(true);

            if (!empty($data)) {
                $this->dataObjectHelper->populateWithArray(
                    $page,
                    $data,
                    \Amasty\ShopbyPage\Api\Data\PageInterface::class
                );
            }
            $this->_coreRegistry->register(RegistryConstants::PAGE, $page);

            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $result = $this->_initAction();
            $result->addBreadcrumb(
                $id ? __('Edit Improved Navigation Page') : __('New Improved Navigation Page'),
                $id ? __('Edit Improved Navigation Page') : __('New Improved Navigation Page')
            );
            $result->getConfig()->getTitle()->prepend(__('Improved Navigation Pages'));

            if ($isExisting) {
                $result->getConfig()->getTitle()->prepend($page->getTitle());
            } else {
                $result->getConfig()->getTitle()->prepend(__('New Improved Navigation Page'));
            }
        }

        return $result;
    }

    /**
     * @param $pageId
     *
     * @return \Amasty\ShopbyPage\Api\Data\PageInterface|bool
     */
    private function loadPage($pageId)
    {
        try {
            $page = $this->_pageRepository->get($pageId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while editing the page.'));
            $page = false;
        }

        return $page;
    }
}
