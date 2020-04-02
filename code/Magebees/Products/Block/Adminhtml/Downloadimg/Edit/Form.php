<?php
namespace Magebees\Products\Block\Adminhtml\Downloadimg\Edit;
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{    
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            array(
                'data' => array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/downloadimg'),
                    'method' => 'post',
			    )
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
