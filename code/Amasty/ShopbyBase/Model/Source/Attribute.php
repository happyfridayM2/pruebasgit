<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Eav\Model\Config as EavConfig;

/**
 * Class Attribute
 * @package Amasty\Shopby\Model\Source
 */
class Attribute implements ArrayInterface
{
    /**
     * @var EavConfig
     */
    protected $_eavConfig;

    /**
     * @var array
     */
    protected $_attributes;

    /**
     * @var int
     */
    protected $_skipAttributeId;

    /**
     * @param EavConfig $eavConfig
     */
    public function __construct(
        EavConfig $eavConfig
    ) {
        $this->_eavConfig = $eavConfig;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray($boolean = 1)
    {
        $optionArray = [];
        $arr = $this->toArray($boolean);
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray($boolean = 1)
    {
        if ($this->_attributes === null) {
            $this->_attributes = [];
            /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection */
            $collection = $this->_eavConfig->getEntityType(
                \Magento\Catalog\Model\Product::ENTITY
            )->getAttributeCollection();

            $collection->join(
                ['catalog_eav' => $collection->getTable('catalog_eav_attribute')],
                'catalog_eav.attribute_id=main_table.attribute_id',
                []
            )->addFieldToFilter('catalog_eav.is_filterable', ['neq' =>  0]);

            if ($this->_skipAttributeId !== null) {
                $collection->addFieldToFilter('main_table.attribute_id', ['neq' => $this->_skipAttributeId]);
            }
            if (!$boolean) {
                $collection->addFieldToFilter('main_table.frontend_input', ['neq' => 'boolean']);
            }
            /** @var \Magento\Eav\Model\Attribute $item */
            foreach ($collection as $item) {
                $this->_attributes[$item->getId()] = $item->getFrontendLabel();
            }
        }

        return $this->_attributes;
    }

    /**
     * @param $skipAttributeId
     * @return $this
     */
    public function skipAttributeId($skipAttributeId)
    {
        $this->_skipAttributeId = $skipAttributeId;
        return $this;
    }
}
