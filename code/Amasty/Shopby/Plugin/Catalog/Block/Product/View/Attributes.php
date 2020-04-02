<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Block\Product\View;

use Amasty\Shopby\Helper\FilterSetting as FilterHelper;
use Magento\Catalog\Block\Product\View\Attributes as ProductViewAttributes;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Attributes
 * @package Amasty\Shopby\Plugin\Catalog\Block\Product\View
 */
class Attributes
{
    const CATEGORY_IDS_ATTRIBUTE_NAME = 'category_ids';

    /**
     * @var FilterHelper
     */
    private $filterHelper;

    /**
     * @var Attribute
     */
    private $eav;

    /**
     * @var PriceCurrencyInterface
     */
    private $currency;

    public function __construct(FilterHelper $filterSetting, Attribute $attribute, PriceCurrencyInterface $currency)
    {
        $this->filterHelper = $filterSetting;
        $this->eav = $attribute;
        $this->currency = $currency;
    }

    /**
     * @param ProductViewAttributes $subject
     * @param  array $excludeAttr = []
     * @return array
     */
    public function beforeGetAdditionalData(ProductViewAttributes $subject, array $excludeAttr = [])
    {
        $excludeAttr[] = self::CATEGORY_IDS_ATTRIBUTE_NAME;
        return [$excludeAttr];
    }

    /**
     * @param ProductViewAttributes $subject
     * @param $data
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function afterGetAdditionalData(ProductViewAttributes $subject, $data)
    {
        $priceAttributeCodes = $this->eav->getAttributeCodesByFrontendType('price');
        foreach ($data as &$row) {
            if (in_array($row['code'], $priceAttributeCodes)) {
                $setting = $this->filterHelper->getSettingByAttributeCode($row['code']);
                if (!$setting->getUnitsLabelUseCurrencySymbol()) {
                    $row['value'] = preg_replace('@<[^>]+>@u', '', $row['value']);
                    $pattern = '@\s*' . preg_quote($this->currency->getCurrencySymbol(), '@') . '\s*@u';
                    $row['value'] = preg_replace($pattern, '', $row['value']);
                    $row['value'] .= ' ' . $setting->getUnitsLabel();
                }
            }
        }
        return $data;
    }
}
