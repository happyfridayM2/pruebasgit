<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\Field\Edit;

use Amasty\Feed\Model\Config\Source\CustomFieldType;
use Magento\Backend\Block\Widget\Form\Generic;

class Conditions extends Generic
{
    /**#@+
     * Keys for DataPersistor and UI
     */
    const FORM_NAMESPACE = 'amfeed_field_form';

    const CONDITION_IDS = 'amdeed_feed_field_condition_ids';
    /**#@-*/

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    private $conditionsBlock;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    private $fieldset;

    /**
     * @var \Amasty\Feed\Model\Field\Condition
     */
    private $condition;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Amasty\Feed\Ui\Component\Form\ProductAttributeOptions
     */
    private $attributeOptions;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $metadata;

    /**
     * @var \Amasty\Feed\Api\CustomFieldsRepositoryInterface
     */
    private $cFieldsRepository;

    /**
     * @var \Amasty\Feed\Model\Config\Source\CustomFieldType
     */
    private $customFieldType;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory
     */
    private $dependencyFieldFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $fieldset,
        \Magento\Framework\App\ProductMetadataInterface $metadata,
        \Amasty\Feed\Ui\Component\Form\ProductAttributeOptions $attributeOptions,
        \Amasty\Feed\Api\CustomFieldsRepositoryInterface $cFieldsRepository,
        \Amasty\Feed\Model\Config\Source\CustomFieldType $customFieldType,
        array $data = []
    ) {
        $this->conditionsBlock = $conditions;
        $this->formFactory = $formFactory;
        $this->fieldset = $fieldset;
        $this->metadata = $metadata;
        $this->dataPersistor = $dataPersistor;
        $this->attributeOptions = $attributeOptions;
        $this->cFieldsRepository = $cFieldsRepository;

        parent::__construct($context, $registry, $formFactory, $data);
        $this->customFieldType = $customFieldType;
    }

    public function toHtml()
    {
        $this->getConditionModel();

        if (version_compare($this->metadata->getVersion(), '2.2.0', '>=')) {
            //Fix for Magento >2.2.0 to display right form layout.
            //Result of compatibility with 2.1.x.
            $this->_prepareLayout();
        }

        $conditionsFieldSetId = $this->condition->getConditionsFieldSetId(self::FORM_NAMESPACE);
        $newChildUrl = $this->getUrl(
            'sales_rule/promo_quote/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => self::FORM_NAMESPACE]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->formFactory->create();
        $renderer = $this->fieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId);

        $fieldset = $form->addFieldset(
            $conditionsFieldSetId,
            []
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions' . $conditionsFieldSetId,
            'text',
            [
                'name' => 'conditions' . $conditionsFieldSetId,
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => true,
                'data-form-part' => self::FORM_NAMESPACE,
            ]
        )->setRule($this->condition)->setRenderer($this->conditionsBlock);

        $fieldset = $form->addFieldset('result', ['legend' => __('Output Value')]);

        $fieldset->addField(
            'result[entity_type]',
            'select',
            [
                'name' => 'rule[result][entity_type]',
                'label' => __('Type'),
                'title' => __('Type'),
                'options' => $this->customFieldType->toArray(),
                'data-form-part' => self::FORM_NAMESPACE
            ]
        );

        $fieldset->addField(
            'result[attribute]',
            'select',
            [
                'name' => 'rule[result][attribute]',
                'label' => __('Attribute'),
                'title' => __('Attribute'),
                'options' => $this->attributeOptions->getOptionsForBlock(),
                'data-form-part' => self::FORM_NAMESPACE,
                'note' => __("If you can't find the needed attribute in the list, please edit the needed attribute. 
                Open the 'Storefront Properties' tab in the attribute edit menu and set 
                'Use for Promo Rule Conditions' field to 'YES'.")
            ]
        );

        $fieldset->addField(
            'result[modify]',
            'text',
            [
                'name' => 'rule[result][modify]',
                'label' => __('Modification'),
                'title' => __('Modification'),
                'placeholder' => __('Percentage (like +15%), or fixed value (like -20)'),
                'data-form-part' => self::FORM_NAMESPACE,
            ]
        );

        $fieldset->addField(
            'result[custom_text]',
            'text',
            [
                'name' => 'rule[result][custom_text]',
                'label' => __('Custom Text'),
                'title' => __('Custom Text'),
                'data-form-part' => self::FORM_NAMESPACE,
            ]
        );

        $dependencies = $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Widget\Form\Element\Dependence::class)
            ->addFieldMap('result[entity_type]', 'result[entity_type]')
            ->addFieldMap('result[attribute]', 'result[attribute]')
            ->addFieldMap('result[modify]', 'result[modify]')
            ->addFieldMap('result[custom_text]', 'result[custom_text]')
            ->addFieldDependence('result[attribute]', 'result[entity_type]', CustomFieldType::ATTRIBUTE)
            ->addFieldDependence('result[modify]', 'result[entity_type]', CustomFieldType::ATTRIBUTE)
            ->addFieldDependence('result[custom_text]', 'result[entity_type]', CustomFieldType::CUSTOM_TEXT);

        $form->setValues($this->condition->getData());
        $this->setConditionFormName($this->condition->getConditions(), self::FORM_NAMESPACE);

        return $form->toHtml() . $dependencies->toHtml();
    }

    /**
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param string $formName
     *
     * @return void
     */
    private function setConditionFormName(\Magento\Rule\Model\Condition\AbstractCondition $conditions, $formName)
    {
        $conditionsFieldSetId = $this->condition->getConditionsFieldSetId(self::FORM_NAMESPACE);
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($conditionsFieldSetId);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
                $condition->setJsFormObject($conditionsFieldSetId);
            }
        }
    }

    private function getConditionModel()
    {
        if ($this->restoreUnsavedData()) {
            return;
        }

        $this->prepareCondition();

        $this->prepareResultData();
    }

    private function prepareResultData()
    {
        $fieldResult = $this->condition->getFieldResult();
        if (empty($fieldResult['attribute'])) {
            $fieldResult['entity_type'] = CustomFieldType::CUSTOM_TEXT;
            $fieldResult['custom_text'] = !empty($fieldResult['modify']) ? $fieldResult['modify'] : '';
            $fieldResult['modify'] = '';
        } else {
            $fieldResult['entity_type'] = CustomFieldType::ATTRIBUTE;
        }

        foreach ($fieldResult as $key => $value) {
            $key = 'result[' . $key . ']';
            $this->condition->setData($key, $value);
        }
    }

    private function prepareCondition()
    {
        $resultId = null;
        $idField = $this->getRequest()->getParam('id');
        $actualConditions = $this->dataPersistor->get(self::CONDITION_IDS);

        if ($actualConditions) {
            $resultId = array_shift($actualConditions);
            $this->dataPersistor->set(self::CONDITION_IDS, $actualConditions);
        } else {
            $ids = $this->cFieldsRepository->getConditionsIds($idField);
            array_pop($ids);
            $resultId = array_shift($ids);
            $this->dataPersistor->set(self::CONDITION_IDS, $ids);
        }

        $this->condition = $this->cFieldsRepository->getConditionModel($resultId);
    }

    /**
     * Try to get unsaved data if error was occurred.
     * Return true if the data was received.
     *
     * @return bool
     */
    private function restoreUnsavedData()
    {
        $this->condition = $this->cFieldsRepository->getConditionModel();
        $tempData = $this->dataPersistor->get(self::FORM_NAMESPACE);

        if ($tempData) {
            $tempData['rule'] = isset($tempData['rule']) ? $tempData['rule'] : [];
            $this->condition->loadPost($tempData['rule']);
            $this->condition->getConditions();
            $this->prepareResultData();

            return true;
        }

        return false;
    }
}
