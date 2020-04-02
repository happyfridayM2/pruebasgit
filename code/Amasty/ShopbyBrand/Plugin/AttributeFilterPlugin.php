<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Plugin;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Amasty\ShopbyBrand\Helper\Content;

/**
 * Class AttributeFilterPlugin
 *
 * @package Amasty\ShopbyBrand\Plugin
 */
class AttributeFilterPlugin
{
    /**
     * @var  Content
     */
    protected $contentHelper;

    /**
     * AttributeFilterPlugin constructor.
     * @param Content $contentHelper
     */
    public function __construct(Content $contentHelper)
    {
        $this->contentHelper = $contentHelper;
    }

    /**
     * @param AbstractFilter $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsVisibleWhenSelected(AbstractFilter $subject, $result)
    {
        return ($result && $this->isBrandingBrand($subject)) ? false : $result;
    }

    /**
     * @param AbstractFilter $subject
     * @param bool $result
     * @return bool
     */
    public function afterShouldAddState(AbstractFilter $subject, $result)
    {
        return ($result && $this->isBrandingBrand($subject)) ? false : $result;
    }

    /**
     * @param AbstractFilter $subject
     * @return bool
     */
    protected function isBrandingBrand(AbstractFilter $subject)
    {
        $brand = $this->contentHelper->getCurrentBranding();
        return $brand &&
            (\Amasty\ShopbyBase\Helper\FilterSetting::ATTR_PREFIX . $subject->getRequestVar() ==
                $brand->getFilterCode());
    }
}
