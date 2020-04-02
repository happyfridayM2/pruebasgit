<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Variables\Edit;

/**
 * Prepare the variables from
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare form
     * @return $this
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $model = $this->_coreRegistry->registry('variable');

        $form->setUseContainer(true);
        $this->setForm($form);
        $form->setHtmlIdPrefix('');

        $fieldset = $form->addFieldset('ordersexporttool_variables_edit_base', ['legend' => __('Create your own variable')]);

        if ($model->getId()) {
            $fieldset->addField('id', "hidden", ['name' => 'id', 'label' => 'id']);
        }

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $dataHelper = $om->get('\Wyomind\OrdersExportTool\Helper\Data');
        $data = $dataHelper->getEntities();
        $options = [];
        foreach ($data as $option) {
            $options[$option['syntax']] = $option['syntax'];
        }

        $fieldset->addField(
            'scope',
            'select',
            [
                'class' => '',
                'id' => 'scope',
                'name' => 'scope',
                'label' => 'Scope',
                'required' => true,
                'options' => $options
            ]
        );

        $fieldset->addField(
            'name',
            'text',
            [
                'class' => 'validate-alphanum',
                'id' => 'name',
                'name' => 'name',
                'label' => 'Name',
                'required' => true
            ]
        );

        $fieldset->addField(
            'comment',
            'textarea',
            [
                'class' => '',
                'id' => 'comment',
                'name' => 'comment',
                'label' => 'Comment',
                'required' => false
            ]
        );

        $scope = ($model->getData('scope')) ? $model->getData('scope') : 'scope';
        $name = ($model->getData('name')) ? $model->getData('name') : 'name';

        $fieldset->addField(
            'script',
            'textarea',
            [
                'class' => 'codemirror',
                'id' => 'script',
                'name' => 'script',
                'label' => 'Php script',
                'required' => false,
                'note' => "This variable can be use as <pre>{{" . $scope . "." . $name . "}}</pre>"
            ]
        );

        if ($this->_coreRegistry->registry('script')) {
            $model->setScript($this->_coreRegistry->registry('script'));
            $this->_coreRegistry->unregister('script');
        }

        $form->setValues($model->getData());
        return parent::_prepareForm();
    }
}