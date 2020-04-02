<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Model\FilterSetting;

use Amasty\ShopbyBase\Model\FilterSetting\AttributeConfig\AttributeListProviderInterface;
use Amasty\ShopbyBrand\Helper\Data as BrandHelper;

/**
 * Class AttributeListProvider
 *
 * @package Amasty\ShopbyBrand\Model\FilterSetting
 */
class AttributeListProvider implements AttributeListProviderInterface
{
    /**
     * @var BrandHelper
     */
    private $helper;

    /**
     * AttributeListProvider constructor.
     * @param BrandHelper $helper
     */
    public function __construct(BrandHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Getting list of attribute codes, which can be configured with Amasty Attribute Settings
     * @return array
     */
    public function getAttributeList()
    {
        return [$this->helper->getBrandAttributeCode() => true];
    }
}
