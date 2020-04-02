<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Block\Adminhtml\Slider\Edit;

use Amasty\ShopbyBase\Block\Adminhtml\Form\Renderer\Fieldset\Element as RenderElement;
use Amasty\ShopbyBrand\Controller\RegistryConstants;

/**
 * Class Form
 * @package Amasty\ShopbyBrand\Block\Adminhtml\Slider\Edit
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var RenderElement
     */
    protected $_renderer;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\ShopbyBase\Block\Adminhtml\Form\Renderer\Fieldset\Element $renderer,
        array $data = []
    ) {
        $this->_renderer = $renderer;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $filterCode = $this->getRequest()->getParam('filter_code');
        $optionId = $this->getRequest()->getParam('option_id');
        $storeId = $this->getRequest()->getParam('store', 0);
        /** @var \Amasty\ShopbyPage\Api\Data\PageInterface $model */
        $model = $this->_coreRegistry->registry(RegistryConstants::FEATURED);
        $urlParams = [
            'option_id' => (int)$optionId,
            'filter_code' => $filterCode,
            'store' => (int)$storeId
        ];
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'class' => 'admin__scope-old',
                    'action' => $this->getUrl('*/*/save', $urlParams),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $form->setUseContainer(true);
        $form->setFieldsetElementRenderer($this->_renderer);
        $form->setDataObject($model);

        $this->_eventManager->dispatch(
            'amshopby_option_form_build_after',
            [
                'form' => $form,
                'setting' => $model,
                'is_slider' => true
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
