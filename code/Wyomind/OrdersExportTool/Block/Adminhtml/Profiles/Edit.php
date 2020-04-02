<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry=null;

    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data=[]
    )
    {
        $this->_coreRegistry=$registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId='id';
        $this->_blockGroup='Wyomind_OrdersExportTool';
        $this->_controller='adminhtml_profiles';

        parent::_construct();

        $this->removeButton('save');
        $this->removeButton('reset');

        $this->updateButton('delete', 'label', __('Delete'));

        if ($this->getRequest()->getParam('id')) {
            $this->addButton(
                'duplicate',
                [
                    'label'=>__('Duplicate'),
                    'class'=>'add',
                    'onclick'=>"jQuery('#id').remove(); jQuery('#back_i').val('1'); jQuery('#edit_form').submit();"
                ]
            );

            $this->addButton(
                'generate',
                [
                    'label'=>__('Generate'),
                    'class'=>'save',
                    'onclick'=>"require(['oet_index'], function (index) { index.generateFromEdit(); })",

                ]
            );
        }

        $this->addButton(
            'save',
            [
                'label'=>__('Save'),
                'class'=>'save',
                'onclick'=>"jQuery('#back_i').val('1'); jQuery('#edit_form').submit();"
            ]
        );
    }
}