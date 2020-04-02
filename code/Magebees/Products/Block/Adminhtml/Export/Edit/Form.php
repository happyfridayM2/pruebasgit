<?php
namespace Magebees\Products\Block\Adminhtml\Export\Edit;
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{    
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            array(
                'data' => array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/export'),
                    'method' => 'post',
			    )
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}