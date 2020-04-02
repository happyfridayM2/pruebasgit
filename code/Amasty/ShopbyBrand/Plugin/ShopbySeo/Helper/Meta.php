<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Plugin\ShopbySeo\Helper;

use Amasty\ShopbyBrand\Helper\Content as ContentHelper;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Magento\Framework\DataObject;

class Meta
{
    /**
     * @var ContentHelper
     */
    private $contentHelper;

    public function __construct(
        ContentHelper $contentHelper
    ) {
        $this->contentHelper = $contentHelper;
        $this->brand = $this->contentHelper->getCurrentBranding();
    }

    /**
     * @param \Amasty\ShopbySeo\Helper\Meta $subject
     * @param \Closure $proceed
     * @param bool $indexTag
     * @param DataObject $data
     * @return bool
     */
    public function aroundGetIndexTagByData(
        \Amasty\ShopbySeo\Helper\Meta $subject,
        \Closure $proceed,
        $indexTag,
        DataObject $data
    ) {
        $keepIndex = $this->isBrandSetting($data['setting']);
        return $keepIndex ? $indexTag : $proceed($indexTag, $data);

    }

    /**
     * @param \Amasty\ShopbySeo\Helper\Meta $subject
     * @param \Closure $proceed
     * @param bool $followTag
     * @param DataObject $data
     * @return bool
     */
    public function aroundGetFollowTagByData(
        \Amasty\ShopbySeo\Helper\Meta $subject,
        \Closure $proceed,
        $followTag,
        DataObject $data
    ) {
        $keepFollow = $this->isBrandSetting($data['setting']);
        return $keepFollow ? $followTag : $proceed($followTag, $data);
    }

    /**
     * @param FilterSettingInterface $setting
     * @return bool
     */
    private function isBrandSetting(FilterSettingInterface $setting)
    {
        $isBrand = false;
        $brand = $this->contentHelper->getCurrentBranding();
        if ($brand && $brand->getFilterCode() == $setting->getFilterCode()) {
            $isBrand = true;
        }

        return $isBrand;
    }
}
