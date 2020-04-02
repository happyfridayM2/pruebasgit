<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Field;

use Amasty\Feed\Model\Config\Source\CustomFieldType;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Conditions
 * Used for conditions block in Condition-Based Attributes
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Condition extends AbstractModel
{
    /**#@+
     * Table columns
     */
    const COLUMN_CONDITION = 'conditions_serialized';
    const COLUMN_RESULT = 'result_serialized';
    const COLUMN_FIELD_ID = 'feed_field_id';
    /**#@-*/

    /**
     * Index for result array
     */
    const RESULT_KEY = 'result';

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Amasty\Feed\Model\Rule\Rule
     */
    private $ruleModel;

    /**
     * @var \Amasty\Feed\Model\Rule\RuleFactory
     */
    private $ruleFactory;

    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Amasty\Feed\Model\Rule\RuleFactory $ruleFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->ruleFactory = $ruleFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Condition::class);
        $this->setIdFieldName('entity_id');
    }

    /**
     * Initialize Promo Rules Model for conditions
     */
    private function initRules()
    {
        if (!$this->ruleModel) {
            $this->ruleModel = $this->ruleFactory->create();
            $this->ruleModel->loadPost($this->getData());
        }
    }

    /**
     * @param string $formName
     *
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditions()
    {
        $this->initRules();

        return $this->ruleModel->getConditions();
    }

    /**
     * @param \Magento\Rule\Model\Condition\Combine $conditions
     */
    public function setCondition($conditions)
    {
        $this->initRules();

        $this->ruleModel->setConditions($conditions);
    }

    /**
     * @return array
     */
    public function getFieldConditions()
    {
        $this->initRules();

        return $this->ruleModel->getConditions()->asArray();
    }

    /**
     * @return array
     */
    public function getFieldResult()
    {
        $result = $this->getData(self::COLUMN_RESULT);
        if (!$result) {
            return [];
        }

        return $this->jsonHelper->jsonDecode($result);
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function loadPost(array $data)
    {
        if (isset($data[self::RESULT_KEY])) {
            if (isset($data[self::RESULT_KEY]['entity_type'])
                && $data[self::RESULT_KEY]['entity_type'] == CustomFieldType::CUSTOM_TEXT
            ) {
                $data[self::RESULT_KEY]['attribute'] = '';
                if (isset($data[self::RESULT_KEY]['custom_text'])) {
                    $data[self::RESULT_KEY]['modify'] = $data[self::RESULT_KEY]['custom_text'];
                }
            }
            unset($data[self::RESULT_KEY]['entity_type']);
            unset($data[self::RESULT_KEY]['custom_text']);

            if (!isset($data[self::RESULT_KEY]['modify'])) {
                $data[self::RESULT_KEY]['modify'] = '';
            }

            $this->setData(self::COLUMN_RESULT, $this->jsonHelper->jsonEncode($data[self::RESULT_KEY]));
            unset($data[self::RESULT_KEY]);
        }

        $this->initRules();
        $this->ruleModel->loadPost($data);
        $this->setData($this->ruleModel->getData());

        return $this;
    }

    /**
     * @param int $fieldId
     *
     * @return $this
     */
    public function beforeSaveCondition($fieldId)
    {
        $this->initRules();

        $this->ruleModel->beforeSave();
        $this->setData(self::COLUMN_CONDITION, $this->ruleModel->getData(self::COLUMN_CONDITION));
        $this->setFeedFieldId($fieldId);

        return $this;
    }

    /**
     * @return int
     */
    public function getFeedFieldId()
    {
        return $this->getData(self::COLUMN_FIELD_ID);
    }

    /**
     * @param int $fieldId
     */
    public function setFeedFieldId($fieldId)
    {
        $this->setData(self::COLUMN_FIELD_ID, $fieldId);
    }
}
