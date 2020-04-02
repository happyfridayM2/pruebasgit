<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Controller\Adminhtml\Group;

use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends \Amasty\Shopby\Controller\Adminhtml\Group
{
    /**
     * @return $this|\Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('group_id')) {
            try {
                $model = $this->groupAttrRepository->get($id);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addError(__('This group no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $model = $this->groupAttrFactory->create();
        }
        $data = $this->sessionFactory->create()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->coreRegistry->register('amshopby_group', $model);

        $resultPage = $this->resultPageFactory->create();

        // 5. Build edit form
        $resultPage->setActiveMenu('Amasty_Shopby::group_attributes')
            ->addBreadcrumb(__('Manage Group Attributes'), __('Manage Group Attributes'))
            ->addBreadcrumb(
                $id ? __('Edit Group') : __('New Group'),
                $id ? __('Edit Group') : __('New Group')
            );
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Group Attributes'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Group'));

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Shopby::group_attributes');
    }
}
