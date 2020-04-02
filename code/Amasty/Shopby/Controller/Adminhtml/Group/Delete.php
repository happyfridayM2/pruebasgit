<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Controller\Adminhtml\Group;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Delete
 * @package Amasty\Shopby\Controller\Adminhtml\Group
 */
class Delete extends \Amasty\Shopby\Controller\Adminhtml\Group
{
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('group_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->groupAttrRepository->get($id);
                $this->groupAttrRepository->delete($model);
                $this->messageManager->addSuccess(__('You deleted the group.'));
                return $resultRedirect->setPath('*/*/');
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addError(__('We can\'t find a group to delete.'));
                return $resultRedirect->setPath('*/*/edit', ['group_id' => $id]);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['group_id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a group to delete.'));

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Shopby::group_attributes');
    }
}
