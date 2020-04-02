<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Block\Adminhtml\Catalog\Product\Attribute;

/**
 * Class Edit
 * @package Amasty\ShopbyBase\Block\Adminhtml\Catalog\Product\Attribute
 */
class Edit extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Amasty\ShopbyBase\Model\Source\DisplayMode
     */
    private $displayModeSource;

    /**
     * @var \Amasty\ShopbyBase\Model\FilterSetting\AttributeConfig
     */
    private $attributeSettingsConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\ShopbyBase\Model\Source\DisplayMode $displayModeSource,
        \Amasty\ShopbyBase\Model\FilterSetting\AttributeConfig $attributeConfig,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->displayModeSource = $displayModeSource;
        $this->attributeSettingsConfig = $attributeConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getFilterCode()
    {
        return \Amasty\ShopbyBase\Helper\FilterSetting::ATTR_PREFIX . $this->getAttributeCode();
    }

    /**
     * @return bool
     */
    public function canConfigureAttributeOptions()
    {
        return $this->attributeSettingsConfig->canBeConfigured($this->getAttributeCode());
    }

    /**
     * @return string
     */
    private function getAttributeCode()
    {
        /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $attribute = $this->coreRegistry->registry('entity_attribute');

        return $attribute ? $attribute->getAttributeCode() : '';
    }
}
