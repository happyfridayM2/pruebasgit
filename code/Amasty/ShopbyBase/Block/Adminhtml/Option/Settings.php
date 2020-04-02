<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Block\Adminhtml\Option;

use Amasty\ShopbyBase\Helper\OptionSetting;

/**
 * @api
 */
class Settings extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Amasty\ShopbyBase\Block\Adminhtml\Form\Renderer\Fieldset\Element
     */
    protected $renderer;

    /**
     * @var OptionSetting
     */
    protected $settingHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\ShopbyBase\Block\Adminhtml\Form\Renderer\Fieldset\Element $renderer,
        OptionSetting $settingHelper,
        array $data = []
    ) {
        $this->renderer = $renderer;
        $this->settingHelper = $settingHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $filterCode = $this->getRequest()->getParam('filter_code');
        $optionId = $this->getRequest()->getParam('option_id');
        $storeId = $this->getRequest()->getParam('store', 0);
        $model = $this->settingHelper->getSettingByValue($optionId, $filterCode, $storeId);
        $model->setCurrentStoreId($storeId);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_options_form',
                    'class' => 'admin__scope-old',
                    'action' => $this->getUrl('*/*/save', [
                        'option_id' => (int)$optionId,
                        'filter_code' => $filterCode,
                        'store' => (int)$storeId
                    ]),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );
        $form->setUseContainer(true);
        $form->setFieldsetElementRenderer($this->renderer);
        $form->setDataObject($model);

        $this->_eventManager->dispatch('amshopby_option_form_build_after', ['form' => $form, 'setting' => $model]);

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
