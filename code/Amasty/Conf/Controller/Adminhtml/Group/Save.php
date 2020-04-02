<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Controller\Adminhtml\Group;

class Save extends \Amasty\Conf\Controller\Adminhtml\Group
{
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = (int)$this->getRequest()->getParam('group_id');
            if ($id) {
                $model = $this->groupAttrRepository->get($id);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This group no longer exists.'));
                    $this->sessionFactory->create()->setFormData($data);
                    return $resultRedirect->setPath('*/*/');
                }
            } else {
                $model = $this->groupAttrFactory->create();
            }

            if (!$id || (($model->getId() && $id) && $model->getGroupCode() != $data['group_code'])) {
                $code = $data['group_code'];
                if ($this->groupAttrFactory->create()->getCollection()
                    ->addFieldToFilter(\Amasty\Conf\Model\GroupAttr::GROUP_CODE, $code)->getSize()
                ) {
                    $this->messageManager->addErrorMessage(__('This group code already exists.'));
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['group_id' => $this->getRequest()->getParam('group_id')]
                    );
                }
            }
            $model->setData($data);
            try {
                $this->groupAttrRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You have saved the group.'));
                $this->sessionFactory->create()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['group_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->sessionFactory->create()->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', ['group_id' => $this->getRequest()->getParam('group_id')]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
