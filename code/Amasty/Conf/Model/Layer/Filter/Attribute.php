<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\Layer\Filter;

use Amasty\Conf\Helper\Group;

class Attribute extends \Magento\CatalogSearch\Model\Layer\Filter\Attribute
{
    /**
     * @var \Magento\Framework\Filter\StripTags
     */
    private $tagFilter;

    /**
     * @var \Amasty\Conf\Model\ResourceModel\GroupAttrOption\CollectionFactory
     */
    private $collectionOptionFactory;

    /**
     * @var \Amasty\Conf\Model\GroupAttrRepository
     */
    private $groupAttrRepository;

    /**
     * @var \Amasty\Conf\Model\ResourceModel\GroupAttr\CollectionFactory
     */
    private $groupCollectionFactory;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        \Amasty\Conf\Model\ResourceModel\GroupAttr\CollectionFactory $groupCollectionFactory,
        \Amasty\Conf\Model\ResourceModel\GroupAttrOption\CollectionFactory $collectionOptionFactory,
        \Amasty\Conf\Model\GroupAttrRepository $groupAttrRepository,
        array $data = []
    ) {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $tagFilter, $data);
        $this->collectionOptionFactory = $collectionOptionFactory;
        $this->groupAttrRepository = $groupAttrRepository;
        $this->tagFilter = $tagFilter;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param   \Magento\Framework\App\RequestInterface $request
     * @return  $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);
        if (empty($attributeValue) && !is_numeric($attributeValue)) {
            return $this;
        }
        $attribute = $this->getAttributeModel();
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();

        $groupModel = $this->groupAttrRepository->getByGroupCode($attributeValue);
        if ($groupModel) {
            if ($groupModel->getOptionIds()) {
                $productCollection->addFieldToFilter(
                    $attribute->getAttributeCode(),
                    ['in' => $groupModel->getOptionIds()]
                );
            }
            $label = $groupModel->getName();
        } else {
            $label = $this->getOptionText($attributeValue);
            $productCollection->addFieldToFilter($attribute->getAttributeCode(), $attributeValue);
        }

        $this->getLayer()
            ->getState()
            ->addFilter($this->_createItem($label, $attributeValue));

        $this->setItems([]); // set items to disable show filtering
        return $this;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();
        $optionsFacetedData = $productCollection->getFacetedData($attribute->getAttributeCode());

        $isAttributeFilterable =
            $this->getAttributeIsFilterable($attribute) === static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS;

        if (count($optionsFacetedData) === 0 && !$isAttributeFilterable) {
            return $this->itemDataBuilder->build();
        }

        $productSize = $productCollection->getSize();

        $options = $attribute->getFrontend()
            ->getSelectOptions();
        /* Amasty functionality start*/
        $this->implementGroupedOptions($options);
        /* end*/
        foreach ($options as $option) {
            $this->buildOptionData($option, $isAttributeFilterable, $optionsFacetedData, $productSize);
        }

        return $this->itemDataBuilder->build();
    }

    /**
     * Build option data
     *
     * @param array $option
     * @param boolean $isAttributeFilterable
     * @param array $optionsFacetedData
     * @param int $productSize
     * @return void
     */
    private function buildOptionData($option, $isAttributeFilterable, $optionsFacetedData, $productSize)
    {
        $value = $this->getOptionValue($option);
        if ($value === false) {
            return;
        }
        /* change function param from $value to $option*/
        $count = $this->getOptionCount($option, $optionsFacetedData);
        if ($isAttributeFilterable && (!$this->isOptionReducesResults($count, $productSize) || $count === 0)) {
            return;
        }
        $this->itemDataBuilder->addItemData(
            $this->tagFilter->filter($option['label']),
            $value,
            $count
        );
    }

    /**
     * Retrieve option value if it exists
     *
     * @param array $option
     * @return bool|string
     */
    private function getOptionValue($option)
    {
        if (empty($option['value']) && !is_numeric($option['value'])) {
            return false;
        }
        return $option['value'];
    }

    /**
     * Retrieve count of the options
     *
     * @param array|string $option
     * @param array $optionsFacetedData
     * @return int
     */
    private function getOptionCount($option, $optionsFacetedData)
    {
        $value = $option['value'];

        return isset($optionsFacetedData[$value]['count'])
            ? (int)$optionsFacetedData[$value]['count']
            : 0;
    }

    private function implementGroupedOptions(&$options)
    {
        $attribute = $this->getAttributeModel();
        $groupOptions = $this->groupCollectionFactory->create()->getGroupsByAttributeId($attribute->getId());
        if ($groupOptions->getSize()) {
            $duplicatedOptions = $this->collectionOptionFactory->create()
                ->getOptionIdsByAttirbuteId($attribute->getId())->getData();

            $duplicatedOptionIds = [];
            $duplicatedOptionRelations = [];
            foreach ($duplicatedOptions as $duplicatedOption) {
                $duplicatedOptionIds[] = $duplicatedOption['option_id'];
                $duplicatedOptionRelations[$duplicatedOption['group_code']][] = $duplicatedOption['option_id'];
            }

            foreach ($options as $key => $option) {
                if (in_array($option['value'], $duplicatedOptionIds)) {
                    unset($options[$key]);
                }
            }

            foreach ($groupOptions as $group) {
                $relatedOptions =  isset($duplicatedOptionRelations[$group->getGroupCode()])?
                    $duplicatedOptionRelations[$group->getGroupCode()]:
                    [];
                $options[] = [
                    'label' => $group->getName(),
                    'value' => Group::LAST_POSSIBLE_OPTION_ID - $group->getId(),
                    'options' => $relatedOptions
                ];
            }
        }
    }
}
