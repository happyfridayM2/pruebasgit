<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Plugin\Shopby\Helper;

use Amasty\ShopbyBrand\Helper\Content as ContentHelper;

/**
 * Class UrlBuilder
 *
 * @package Amasty\ShopbyBrand\Plugin\Shopby\Helper
 */
class UrlBuilder
{
    /**
     * @var ContentHelper
     */
    private $contentHelper;

    /**
     * @var \Amasty\ShopbyBase\Api\Data\OptionSettingInterface
     */
    private $brand;

    public function __construct(
        ContentHelper $contentHelper
    ) {
        $this->contentHelper = $contentHelper;
        $this->brand = $this->contentHelper->getCurrentBranding();
    }

    /**
     * @param $subject
     * @param $result
     * @return bool
     */
    public function afterIsGetDefaultUrl($subject, $result)
    {
        return $result && $this->brand == null;
    }
}
