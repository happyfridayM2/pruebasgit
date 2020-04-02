<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Controller\Adminhtml\Group;

class Edit extends \Amasty\Conf\Controller\Adminhtml\Group
{
    /**
     * @return $this|\Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('group_id');
        $model = $this->groupAttrFactory->create();
        if ($id) {
            $model = $this->groupAttrRepository->get($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This group no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->sessionFactory->create()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->coreRegistry->register('amconf_group', $model);

        $resultPage = $this->resultPageFactory->create();

        // 5. Build edit form
        $resultPage->setActiveMenu('Amasty_Conf::group_attributes')
            ->addBreadcrumb(__('Groups'), __('Groups'))
            ->addBreadcrumb(
                $id ? __('Edit Group') : __('New Group'),
                $id ? __('Edit Group') : __('New Group')
            );
        $resultPage->getConfig()->getTitle()->prepend(__('Groups'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Group'));

        return $resultPage;
    }
}
