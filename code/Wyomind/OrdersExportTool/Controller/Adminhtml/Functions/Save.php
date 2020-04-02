<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Functions;

/**
 * Save action
 */
class Save extends AbstractFunctions
{
    /**
     * Execute action
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        $return = $this->resultRedirectFactory->create()->setPath('ordersexporttool/functions/index');

        if ($data) {
            $model = $this->_objectManager->create($this->model);
            $id = $data['id'];
            if ($id) {
                $model->setId($id);
            }

            foreach ($data as $index => $value) {
                $model->setData($index, $value);
            }

            if (!$this->_validatePostData($data)) {
                $return = $this->resultRedirectFactory->create()->setPath('ordersexporttool/functions/edit', ['id' => $model->getId(), '_current' => true]);
            } else {
                $displayErrors = ini_get('display_errors');
                ini_set('display_errors', 0);
                try {
                    if ($this->helperData->execPhp($this->getRequest()->getParam('script'),"?><?php function(){" . substr(trim($this->getRequest()->getParam('script')), 5, -2) . "} ?>") === false) {
                        $this->_coreRegistry->register('script', $data['script']);
                        $this->messageManager->addError(__("Invalid function declaration") . "<br>" . error_get_last()["message"]);
                        $return = $this->_resultForwardFactory->create()->forward("edit");
                    }
                    
                    $model->save();
                    $this->messageManager->addSuccess(__('The function has been saved.'));
                    $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('Unable to save the function.') . '<br/><br/>' . $e->getMessage());
                }
                ini_set('display_errors', $displayErrors);
                $return = $this->resultRedirectFactory->create()->setPath('ordersexporttool/functions/edit', ['id' => $model->getId(), '_current' => true]);
            }
        } else {
            $return = $this->resultRedirectFactory->create()->setPath('ordersexporttool/functions/index');
        }

        return $return;
    }
}