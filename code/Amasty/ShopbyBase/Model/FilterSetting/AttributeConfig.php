<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model\FilterSetting;

use \Amasty\ShopbyBase\Model\FilterSetting\AttributeConfig\AttributeListProviderInterface;

/**
 * Class AttributeConfig
 * @package Amasty\ShopbyBase\Model\FilterSetting
 */
class AttributeConfig
{
    const ALL_ATTRIBUTES_PARAM = 'all';

    /**
     * @var array
     */
    private $attributeList = [];

    /**
     * AttributeConfig constructor.
     * @param array $attributeList = []
     * @param array $attributeProviders = []
     */
    public function __construct(array $attributeList = [], array $attributeProviders = [])
    {
        $this->attributeList = $attributeList;
        foreach ($attributeProviders as $provider) {
            if ($provider instanceof AttributeListProviderInterface) {
                $this->attributeList += $provider->getAttributeList();
            }
        }
    }

    /**
     * Check if attribute can be configured
     *
     * @param string $attributeCode
     * @return bool
     */
    public function canBeConfigured($attributeCode)
    {
        if (isset($this->attributeList[self::ALL_ATTRIBUTES_PARAM])) {
            return (bool)$this->attributeList[self::ALL_ATTRIBUTES_PARAM];
        }
        $canBeConfigured = isset($this->attributeList[$attributeCode]) ? $this->attributeList[$attributeCode] : false;
        return (bool)$canBeConfigured;
    }
}
