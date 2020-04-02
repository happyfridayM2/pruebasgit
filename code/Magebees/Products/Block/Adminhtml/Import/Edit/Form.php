<?php
namespace Magebees\Products\Block\Adminhtml\Import\Edit;
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_customerAccountService;
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            array(
                'data' => array(
                    'id' => 'edit_form',
                    'enctype' => 'multipart/form-data'
                )
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}