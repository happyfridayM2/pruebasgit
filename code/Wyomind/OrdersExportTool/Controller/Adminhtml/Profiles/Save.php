<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Profiles;

/**
 * Save action
 */
class Save extends AbstractProfiles
{
    /**
     * Execute action
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();

        if (isset($data['store_id'])) {
            if (is_array($data['store_id'])) {
                $data['store_id'] = implode(',', $data['store_id']);
            }
        }

        if (isset($data['product_type'])) {
            if (is_array($data['product_type'])) {
                $data['product_type'] = implode(',', $data['product_type']);
            }
        }

        $return = $this->resultRedirectFactory->create()->setPath('ordersexporttool/profiles/index');

        if ($data) {
            $model = $this->_objectManager->create($this->model);
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->set($id);
            }

            $toSanitize = [
                'name',
                'last_exported_id',
                'update_status_message',
                'path',
                'ftp_host',
                'ftp_login',
                'ftp_password',
                'ftp_dir',
                'mail_recipients',
                'mail_subject',
                'mail_message'
            ];

            foreach ($data as $index => $value) {
                if (in_array($index, $toSanitize)) {
                    $value = $this->helperData->stripTagsContent($value);
                }
                $model->setData($index, $value);
            }

            if (!$this->_validatePostData($data)) {
                $return = $this->resultRedirectFactory->create()->setPath('ordersexporttool/profiles/edit', ['id' => $model->getId(), '_current' => true]);
                return $return;
            } else {
                try {

                    $model->save();
                    $this->messageManager->addSuccess(__('The profile has been saved.'));
                    $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                    if ($this->getRequest()->getParam('back_i') === "1") {
                        return $this->resultRedirectFactory->create()->setPath('ordersexporttool/profiles/edit', ['id' => $model->getId(), '_current' => true]);
                    } elseif ($this->getRequest()->getParam('generate_i') === "1") {
                        $this->getRequest()->setParam('id', $model->getId());
                       return $this->_resultForwardFactory->create()->forward("generate");
                    }
                    $this->_getSession()->setFormData($data);
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('Unable to save the profile.') . '<br/><br/>' . $e->getMessage());
                    return $this->resultRedirectFactory->create()->setPath('ordersexporttool/profiles/edit', ['id' => $model->getId(), '_current' => true]);
                }
            }
        }


    }
}