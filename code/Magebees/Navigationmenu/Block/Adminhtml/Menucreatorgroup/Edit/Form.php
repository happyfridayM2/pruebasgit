<?php
namespace Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
   
    protected $_customerAccountService;

    protected function _prepareForm()
    {
      
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
