<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter;

/**
 * Class AdditionalFieldMapper
 * @package Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter
 */
class AdditionalFieldMapper
{
    const ES_DATA_TYPE_STRING = 'string';
    const ES_DATA_TYPE_TEXT = 'text';
    const ES_DATA_TYPE_FLOAT = 'float';
    const ES_DATA_TYPE_INT = 'integer';
    const ES_DATA_TYPE_DATE = 'date';

    /** @deprecated */
    const ES_DATA_TYPE_ARRAY = 'array';

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * AdditionalFieldMapper constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $fields
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $fields = []
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->fields = $fields;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllAttributesTypes($subject, array $result)
    {
        foreach ($this->fields as $fieldName => $fieldType) {
            if (is_object($fieldType) && ($fieldType instanceof AdditionalFieldMapperInterface)) {
                $attributeTypes = $fieldType->getAdditionalAttributeTypes();
                // @codingStandardsIgnoreLine
                $result = array_merge($result, $attributeTypes);
                continue;
            }

            if (empty($fieldName)) {
                continue;
            }
            if ($this->isValidFieldType($fieldType)) {
                $result[$fieldName] = ['type' => $fieldType];
            }
        }

        return $result;
    }

    /**
     * Amasty Elastic entity builder plugin
     *
     * @param mixed $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuildEntityFields($subject, array $result)
    {
        return $this->afterGetAllAttributesTypes($subject, $result);
    }

    /**
     * @param mixed $subject
     * @param callable $proceed
     * @param string $attributeCode
     * @param array $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return string
     */
    public function aroundGetFieldName($subject, callable $proceed, $attributeCode, $context = [])
    {
        if (isset($this->fields[$attributeCode]) && is_object($this->fields[$attributeCode])) {
            $filedMapper = $this->fields[$attributeCode];
            if ($filedMapper instanceof AdditionalFieldMapperInterface) {
                return $filedMapper->getFiledName($context);
            }
        }
        return $proceed($attributeCode, $context);
    }

    /**
     * @param mixed $subject
     * @param callable $proceed
     * @param BucketInterface $bucket
     * @return string
     */
    public function aroundMapFieldName($subject, callable $proceed, $fieldName)
    {
        if (isset($this->fields[$fieldName]) && is_object($this->fields[$fieldName])) {
            $filedMapper = $this->fields[$fieldName];
            if ($filedMapper instanceof AdditionalFieldMapperInterface) {
                $context = [
                    'customerGroupId' => $this->customerSession->getCustomerGroupId(),
                    'websiteId'       => $this->storeManager->getWebsite()->getId()
                ];
                return $filedMapper->getFiledName($context);
            }
        }
        return $proceed($fieldName);
    }

    /**
     * @param $fieldType
     * @return bool
     */
    private function isValidFieldType($fieldType)
    {
        switch ($fieldType) {
            case self::ES_DATA_TYPE_STRING:
            case self::ES_DATA_TYPE_DATE:
            case self::ES_DATA_TYPE_INT:
            case self::ES_DATA_TYPE_FLOAT:
                break;
            default:
                $fieldType = false;
                break;
        }
        return $fieldType;
    }
}
