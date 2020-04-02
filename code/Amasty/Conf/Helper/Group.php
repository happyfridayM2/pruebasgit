<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Helper;

use Magento\Framework\App\Helper\Context;

class Group extends \Magento\Framework\App\Helper\AbstractHelper
{
    const LAST_POSSIBLE_OPTION_ID = (2 << 31) - 1;

    /**
     * @var \Amasty\Conf\Model\ResourceModel\GroupAttr\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var \Amasty\Shopby\Model\GroupAttr\DataProvider
     */
    private $groupAttributeDataProvider;

    public function __construct(
        Context $context,
        \Amasty\Conf\Model\ResourceModel\GroupAttr\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Base\Model\Serializer $serializer,
        \Amasty\Conf\Model\GroupAttr\DataProviderFactory $dataProviderFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->groupAttributeDataProvider = $dataProviderFactory->create();
        parent::__construct($context);
    }

    /**
     * @param $id
     * @return array
     */
    public function getGroupsWithOptions($id = null)
    {
        $collection = $this->getGroupCollection($id)->joinOptions();

        return $this->scopeData($collection, 'options', 'option_id');
    }

    /**
     * @param $attributeId
     * @return \Amasty\Conf\Model\ResourceModel\GroupAttr\Collection
     */
    public function getGroupCollection($attributeId = null)
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('enabled', 1)
            ->addOrder('position', \Magento\Framework\Data\Collection\AbstractDb::SORT_ORDER_ASC);
        if ($attributeId) {
            $collection->addFieldToFilter('attribute_id', $attributeId);
        }

        return $collection;
    }

    /**
     * @param $attributeId
     * @return \Amasty\Conf\Api\Data\GroupAttrInterface[]
     */
    public function getGroupsByAttributeId($attributeId)
    {
        return $this->groupAttributeDataProvider->getGroupsByAttributeId($attributeId);
    }

    /**
     * @param $collection
     * @param $config
     * @param $field
     * @return array
     */
    protected function scopeData($collection, $config, $field)
    {
        $options = [];
        if ($collection->getSize()) {
            foreach ($collection->getData() as $data) {
                if (!isset($options[$data['group_id']])) {
                    $options[$data['group_id']] = [
                        'code' => $data['group_code'],
                        'label' => $data['name'],
                        $config => []
                    ];
                }
                $options[$data['group_id']][$config][] = $data[$field];
            }
        }

        return $options;
    }

    /**
     * @param $attributeId
     * @return array
     */
    public function getGroupAttributeRanges($attributeId)
    {
        $groupRanges = [];
        $groups = $this->getGroupsByAttributeId($attributeId);
        foreach ($groups as $group) {
            if ($group->hasValues()) {
                $values = $group->getValues();
                $groupRanges[$group->getGroupCode()] = $this->getMinMaxValues($values);
            }
        }

        return $groupRanges;
    }

    public function getGroupAttributeMinMaxRanges($attributeId)
    {
        $minMaxRanges = [];
        $groupRanges = $this->getGroupAttributeRanges($attributeId);
        foreach ($groupRanges as $groupCode => $groupRange) {
            $minMaxRanges[$groupRange['min'] . '-' . $groupRange['max']] = $groupCode;
        }
        return $minMaxRanges;
    }

    /**
     * @param \Amasty\Conf\Api\Data\GroupAttrOptionInterface[] $option
     * @return array
     */
    public function getMinMaxValues($groupValues)
    {
        $min = $groupValues[0]->getValue();
        $max = $groupValues[1]->getValue();
        if ($max < $min) {
            $buffer = $min;
            $min = $max;
            $max = $buffer;
        }

        return ['min' => $min, 'max' => $max];
    }

    /**
     * @param \Amasty\Conf\Api\Data\GroupAttrInterface[] $groups
     * @param string $value
     * @return array
     */
    public function getGroupOptionsByCode($groups, $value)
    {
        foreach ($groups as $group) {
            if ($group->getGroupCode() == $value && $group->hasOptions()) {
                $options = $group->getOptions();
                return array_map(function ($option) {
                    return $option->getOptionId();
                }, $options);
            }
        }

        return [];
    }

    /**
     * @param $attributeId
     * @param $groupCode
     *
     * @return null|string
     */
    public function getGroupLabel($attributeId, $groupCode)
    {
        $groups = $this->getGroupsByAttributeId($attributeId);
        foreach ($groups as $group) {
            if ($group->getGroupCode() == $groupCode) {
                return $group->getName();
            }
        }

        return null;
    }

    /**
     * @param int $attributeId
     *
     * @return array
     */
    public function getAliasGroup($attributeId)
    {
        $data = [];
        $groups = $this->getGroupsByAttributeId($attributeId);

        foreach ($groups as $group) {
            $url = $group->getUrl();
            if (!$url) {
                $url = $group->getGroupCode();
            }
            $data[$group->getGroupCode()] = $url;
        }

        return $data;
    }

    /**
     * @param $label
     * @param int|null $storeId
     *
     * @return string
     */
    public function chooseGroupLabel($label, $storeId = null)
    {
        $storeId = $storeId ?: $this->storeManager->getStore()->getId();
        if (preg_match('/^\{\s*\".*\}$/', $label) || preg_match('/s:([0-9]+):\"(.*?)\";/', $label)) {
            $labels = $this->serializer->unserialize($label);
            if (isset($labels[$storeId])) {
                $label = $labels[$storeId] ?: $this->chooseDefaultLabel($labels);
            } else {
                $label = $this->chooseDefaultLabel($labels);
            }
        }

        return $label;
    }

    /**
     * @param $labels
     *
     * @return string
     */
    private function chooseDefaultLabel($labels)
    {
        return isset($labels[$this->storeManager->getDefaultStoreView()->getId()])
            ? $labels[$this->storeManager->getDefaultStoreView()->getId()]
            : array_shift($labels);
    }
}
