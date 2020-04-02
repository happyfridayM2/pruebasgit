<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Api;

interface CustomFieldsRepositoryInterface
{
    /**
     * @param \Amasty\Feed\Model\Field\Field $field
     *
     * @return \Amasty\Feed\Model\Field\Field
     */
    public function saveField($field);

    /**
     * @param \Amasty\Feed\Model\Field\Condition $condition
     * @param int $fieldId
     *
     * @return void
     */
    public function saveCondition($condition, $fieldId);

    /**
     * @param int $fieldId
     * @param bool $deleteField
     *
     * @return void
     */
    public function deleteAllConditions($fieldId, $deleteField = false);

    /**
     * @param int $conditionId
     *
     * @return \Amasty\Feed\Model\Field\Condition
     */
    public function getConditionModel($conditionId = null);

    /**
     * @param int $fieldId
     *
     * @return array
     */
    public function getConditionsIds($fieldId);

    /**
     * @param int $fieldId
     *
     * @return \Amasty\Feed\Model\Field\Field
     */
    public function getFieldModel($fieldId = null);
}
