<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Controller\Adminhtml\Profiles;

/**
 * Generate action
 */
class Generate extends AbstractProfiles
{
    /**
     * Execute action
     */
    public function execute()
    {

        if ($this->getRequest()->getParam("isAjax")) {
            session_write_close();
        }

        $request=$this->getRequest();
        $id=$request->getParam('id');


        $model=$this->_objectManager->create($this->model);

        $model->limit=0;
        if ($model->load($id)) {
            try {
                $model->generate($request);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Unable to generate the export file.') . '<br/><br/>' . nl2br($e->getMessage()));
            }
        } else {
            $this->messageManager->addError(__('Unable to find a profile to generate.'));
        }

        if ($request->getParam('generate_i')) {
            $resultForward=$this->_resultForwardFactory->create();
            $resultForward->setParams(['id'=>$id]);
            return $resultForward->forward('edit');
        }


    }
}