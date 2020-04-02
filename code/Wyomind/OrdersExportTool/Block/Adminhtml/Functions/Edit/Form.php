<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Functions\Edit;

/**
 * Backend form widget
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $model = $this->_coreRegistry->registry('function');

        $form->setUseContainer(true);
        $this->setForm($form);

        $form->setHtmlIdPrefix('');

        $fieldset = $form->addFieldset('ordersexporttool_functions_edit_base', ['legend' => __('Create your own function based on PHP')]);

        if ($model->getId()) {
            $fieldset->addField('id', "hidden", ['name' => 'id', 'label' => 'id']);
        }

        // ===================== all needed urls ===============================
        $fieldset->addField(
            'script',
            'textarea',
            [
                'class' => 'codemirror',
                'id' => 'script',
                'name' => 'script',
                'label' => 'Php script',
                'required' => false,
                'note' => "Example : <br><pre>function example(\$self,\$argument_1,\$argument_2){<br>" . "&nbsp;&nbsp;&nbsp;&nbsp;... do something with the arguments...<br>" . "&nbsp;&nbsp;&nbsp;&nbsp;return \$something;<br>" . "}</pre><br>" . "This custom function can be used in the template as follows: <br>" . "<pre>{{any.variable output=\"example(\$self,'value 1','value 2')\"}}</pre>",
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
