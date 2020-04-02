<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Adminhtml\Group\Edit;

/**
 * Adminhtml cms block edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Amasty\Shopby\Model\Source\Attribute
     */
    protected $attribute;

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
        \Amasty\Shopby\Model\Source\Attribute $attribute,
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
        $model = $this->_coreRegistry->registry('amshopby_group');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('group_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('group_id', 'hidden', ['name' => 'group_id']);
        }

        $groupTitle = $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Group Title'), 'title' => __('Group Title'), 'required' => true]
        );

        $groupTitle->setRenderer(
            $this->getLayout()->createBlock(\Amasty\Shopby\Block\Adminhtml\Form\Renderer\Fieldset\MultiStore::class)
                ->setName('name')
        );

        $fieldset->addField(
            'group_code',
            'text',
            ['name' => 'group_code', 'label' => __('Group Code'), 'title' => __('Group Code'), 'required' => true]
        );

        $fieldset->addField(
            'enabled',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'enabled',
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );

        $visualField = $fieldset->addField(
            'visual',
            'text',
            [
                'name' => 'visual',
                'label' => __('Swatch'),
                'title' => __('Swatch')
            ]
        );

        $fieldset->addField(
            'type',
            'hidden',
            ['name' => 'type']
        );

        $fieldset->addField(
            'url',
            'text',
            ['name' => 'url', 'label' => __('Alias'), 'title' => __('Alias')]
        );

        $fieldset->addField(
            'position',
            'text',
            ['name' => 'position', 'label' => __('Position'), 'title' => __('Position')]
        );

        $fieldset->addField(
            'attribute_id',
            'select',
            [
                'name'     => 'attribute_id',
                'label'    => __('Attribute'),
                'title'    => __('Attribute'),
                'values'   => $this->attribute->toOptionArray(0),
            ]
        );
        $attributeOptions = $fieldset->addField(
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
                ->createBlock(\Amasty\Shopby\Block\Adminhtml\Group\Edit\Renderer\Visual::class)
        );
        
        $attributeOptions->setRenderer(
            $this->getLayout()
                ->createBlock(\Amasty\Shopby\Block\Adminhtml\Group\Edit\Renderer\Options::class)
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
