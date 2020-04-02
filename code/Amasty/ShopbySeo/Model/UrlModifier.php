<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Model;

/**
 * Class UrlModifier
 * @package Amasty\ShopbySeo\Model
 */
class UrlModifier implements \Amasty\ShopbyBase\Api\UrlModifierInterface
{
    /**
     * @var \Amasty\ShopbySeo\Helper\Url
     */
    private $urlHelper;

    public function __construct(\Amasty\ShopbySeo\Helper\Url $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param string $url
     * @return string
     */
    public function modifyUrl($url)
    {
        return $this->urlHelper->seofyUrl($url);
    }
}
