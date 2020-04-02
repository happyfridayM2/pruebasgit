<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Controller\Adminhtml\Page;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Amasty\ShopbyPage\Model\ResourceModel\Page\CollectionFactory;
use Amasty\ShopbyPage\Api\PageRepositoryInterface;

/**
 * Class MassDelete
 *
 * @package Amasty\ShopbyPage\Controller\Adminhtml\Page
 */
class MassDelete extends Action
{
    /**
     * @var  CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Filter $filter,
        PageRepositoryInterface $pageRepository
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->pageRepository = $pageRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        /** @var \Amasty\ShopbyPage\Model\Page $page */
        foreach ($collection as $page) {
            $this->pageRepository->deleteById($page->getId());
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShopbyPage::page');
    }
}
