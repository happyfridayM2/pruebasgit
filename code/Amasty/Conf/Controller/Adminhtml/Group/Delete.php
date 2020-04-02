<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Controller\Adminhtml\Group;

class Delete extends \Amasty\Conf\Controller\Adminhtml\Group
{
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('group_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->groupAttrRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You have deleted the group.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['group_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a group to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
