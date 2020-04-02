<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog;

use Amasty\Shopby\Helper\FilterSetting as FilterSettingHelper;
use Amasty\Shopby\Model\Source\DisplayMode;

/**
 * Class Swatches
 * @package Amasty\Shopby\Plugin\Catalog
 */
class Swatches
{
    /**
     * @var FilterSettingHelper
     */
    private $filterSettingHelper;

    public function __construct(
        FilterSettingHelper $filterSettingHelper
    ) {
        $this->filterSettingHelper = $filterSettingHelper;
    }

    /**
     * @param \Magento\Swatches\Helper\Data $subject
     * @param \Closure $closure
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return bool
     */
    public function aroundIsSwatchAttribute(
        \Magento\Swatches\Helper\Data $subject,
        \Closure $closure,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
    ) {
        $isSwatchAttribute = $closure($attribute);
        if (!$isSwatchAttribute) {
            $filterSetting = $this->filterSettingHelper->getSettingByAttributeCode($attribute->getAttributeCode());
            $isSwatchAttribute = in_array(
                $filterSetting->getDisplayMode(),
                [DisplayMode::MODE_IMAGES_LABELS, DisplayMode::MODE_IMAGES]
            );
        }

        return $isSwatchAttribute;
    }
}
