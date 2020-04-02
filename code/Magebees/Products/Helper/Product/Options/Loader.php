<?php
namespace Magebees\Products\Helper\Product\Options;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;

class Loader extends \Magento\ConfigurableProduct\Helper\Product\Options\Loader
{    
    private $extensionAttributesJoinProcessor;
    private $optionValueFactory;
    public function __construct(
        OptionValueInterfaceFactory $optionValueFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->optionValueFactory = $optionValueFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }
    public function load(ProductInterface $product)
    {
        $options = [];
        /** @var Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        if (get_class($typeInstance) == 'Magento\Catalog\Model\Product\Type\Simple' || get_class($typeInstance) == 'Magento\Bundle\Model\Product\Type')
        {
        return null;
        }
        $attributeCollection = $typeInstance->getConfigurableAttributeCollection($product);
        $this->extensionAttributesJoinProcessor->process($attributeCollection);
        foreach ($attributeCollection as $attribute) {
            $values = [];
            $attributeOptions = $attribute->getOptions();
            if (is_array($attributeOptions)) {
                foreach ($attributeOptions as $option) {
                    /** @var \Magento\ConfigurableProduct\Api\Data\OptionValueInterface $value */
                    $value = $this->optionValueFactory->create();
                    $value->setValueIndex($option['value_index']);
                    $values[] = $value;
                }
            }
            $attribute->setValues($values);
            $options[] = $attribute;
        }
        return $options;
    }
}