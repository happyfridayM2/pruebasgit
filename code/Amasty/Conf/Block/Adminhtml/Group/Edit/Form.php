<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Block\Adminhtml\Group\Edit;

use Amasty\Conf\Model\ResourceModel\GroupAttr;

/**
 * Adminhtml cms block edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    private $systemStore;

    /**
     * @var \Amasty\Conf\Model\Source\Attribute
     */
    private $attribute;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Amasty\Conf\Model\Source\Attribute $attribute,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->attribute = $attribute;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('group_form');
        $this->setTitle(__('Group Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('amconf_group');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]
        );

        $form->setHtmlIdPrefix('group_');

        $fieldSet = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('General Information'),
                'class' => 'fieldset-wide'
            ]
        );

        if ($model && $model->getId()) {
            $fieldSet->addField('group_id', 'hidden', ['name' => 'group_id']);
        }

        $fieldSet->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Group Title'),
                'title' => __('Group Title'),
                'required' => true
            ]
        );

        $fieldSet->addField(
            'group_code',
            'text',
            [
                'name' => 'group_code',
                'label' => __('Group Code'),
                'title' => __('Group Code'),
                'required' => true
            ]
        );

        $fieldSet->addField(
            'enabled',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'enabled',
                'required' => true,
                'options' => [
                    GroupAttr::IS_ENABLED => __('Enabled'),
                    GroupAttr::IS_DISABLED => __('Disabled')
                ]
            ]
        );

        $visualField = $fieldSet->addField(
            'visual',
            'text',
            [
                'name' => 'visual',
                'label' => __('Swatch'),
                'title' => __('Swatch')
            ]
        );

        $fieldSet->addField(
            'type',
            'hidden',
            ['name' => 'type']
        );

        $fieldSet->addField(
            'position',
            'text',
            [
                'name' => 'position',
                'label' => __('Position'),
                'title' => __('Position')
            ]
        );

        $fieldSet->addField(
            'attribute_id',
            'select',
            [
                'name'     => 'attribute_id',
                'label'    => __('Attribute'),
                'title'    => __('Attribute'),
                'values'   => $this->attribute->toOptionArray(),
            ]
        );
        $attributeOptions = $fieldSet->addField(
            'attribute_options',
            'text',
            [
                'name'     => 'attribute_options',
                'label'    => __('Attribute Options'),
                'title'    => __('Attribute Options')
            ]
        );

        $visualField->setRenderer(
            $this->getLayout()
                ->createBlock(\Amasty\Conf\Block\Adminhtml\Group\Edit\Renderer\Visual::class)
        );
        
        $attributeOptions->setRenderer(
            $this->getLayout()
                ->createBlock(\Amasty\Conf\Block\Adminhtml\Group\Edit\Renderer\Options::class)
        );
        if (!$model->getId()) {
            $model->setData('enabled', '1');
        }
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
