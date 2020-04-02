<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\Source;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Option\ArrayInterface;

class Attribute implements ArrayInterface
{
    /**
     * @var array
     */
    protected $_attributes;

    /** @var int */
    protected $_skipAttributeId;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler
     */
    private $configurableAttributeHandler;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $collection;

    /**
     * @param EavConfig $eavConfig
     */
    public function __construct(
        EavConfig $eavConfig,
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler
    ) {
        $this->eavConfig = $eavConfig;
        $this->configurableAttributeHandler = $configurableAttributeHandler;
        $this->collection = $configurableAttributeHandler->getApplicableAttributes();
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->collection->getItems() as $attribute) {
            if ($this->configurableAttributeHandler->isAttributeApplicable($attribute)
                && $attribute->getAttributeId() !== $this->_skipAttributeId
            ) {

                $optionArray[] = [
                    'value' => $attribute->getAttributeId(),
                    'label' => $attribute->getFrontendLabel()
                ];
            }
        }

        return $optionArray;
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
