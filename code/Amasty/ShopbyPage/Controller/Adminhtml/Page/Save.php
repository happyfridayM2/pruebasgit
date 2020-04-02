<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Controller\Adminhtml\Page;

use Magento\Backend\App\Action;
use Amasty\ShopbyPage\Api\Data\PageInterfaceFactory;
use Amasty\ShopbyPage\Api\PageRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;

/**
 * Class Save
 *
 * @package Amasty\ShopbyPage\Controller\Adminhtml\Page
 */
class Save extends Action
{
    /**
     * @var PageInterfaceFactory
     */
    protected $pageDataFactory;

    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    public function __construct(
        Action\Context $context,
        PageInterfaceFactory $pageDataFactory,
        PageRepositoryInterface $pageRepository,
        DataObjectHelper $dataObjectHelper,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->pageDataFactory = $pageDataFactory;
        $this->pageRepository = $pageRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;

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
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $id = $this->getRequest()->getParam('page_id', false);

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            try {
                $pageData = $this->pageDataFactory->create();

                if ($id) {
                    $flatArray = $this->extensibleDataObjectConverter->toNestedArray(
                        $this->pageRepository->get($id),
                        [],
                        \Amasty\ShopbyPage\Api\Data\PageInterface::class
                    );

                    if (!isset($data['conditions'])) {
                        unset($flatArray['conditions']);
                    }
                    $data = array_merge($flatArray, $data);
                }

                $this->dataObjectHelper->populateWithArray(
                    $pageData,
                    $data,
                    \Amasty\ShopbyPage\Api\Data\PageInterface::class
                );

                $this->validateConditions($data);

                if (isset($data['image_delete'])) {
                    $pageData->removeImage();
                    $pageData->setImage(null);
                }

                try {
                    $imageName = $pageData->uploadImage('image');
                    $pageData->setImage($imageName);
                } catch (\Exception $e) {
                    if ($e->getCode() != Uploader::TMP_NAME_EMPTY) {
                        $this->messageManager->addErrorMessage(__('Image file was not uploaded'));
                    }
                }

                $pageData = $this->pageRepository->save($pageData);

                $this->messageManager->addSuccessMessage(__('You saved this page.'));
                $this->_session->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $pageData->getPageId(), '_current' => true]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
            }

            $this->_getSession()->setFormData($data);

            if ($id) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            } else {
                return $resultRedirect->setPath('*/*/new');
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param array $data
     *
     * @throws LocalizedException
     */
    protected function validateConditions(array $data)
    {
        if (!isset($data['conditions'])) {
            throw new LocalizedException(__('Please select the Filter Conditions'));
        }
    }
}
