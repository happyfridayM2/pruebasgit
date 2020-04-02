<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Theme\Block\Html\Header;

/**
 * Class Logo
 * @package Amasty\Shopby\Plugin\Theme\Block\Html\Header
 */
class Logo
{
    const SHOPBY_ROUTE_NAME = 'amshopby';
    const BRAND_ROUTE_NAME = 'ambrand';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    public function __construct(\Magento\Framework\App\Request\Http $request)
    {
        $this->request = $request;
    }

    /**
     * @param \Magento\Theme\Block\Html\Header\Logo $subject
     * @param \Closure $closure
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundIsHomePage(\Magento\Theme\Block\Html\Header\Logo $subject, \Closure $closure)
    {
        if (in_array($this->request->getRouteName(), [self::SHOPBY_ROUTE_NAME, self::BRAND_ROUTE_NAME])) {
            return false;
        }
        return $closure();
    }
}
