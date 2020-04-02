<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Theme\Block\Html;

/**
 * Class Pager
 * @package Amasty\Shopby\Plugin\Theme\Block\Html
 */
class Pager
{
    /**
     * @var \Amasty\ShopbyBase\Model\UrlBuilder
     */
    private $urlBuilder;

    public function __construct(\Amasty\ShopbyBase\Model\UrlBuilder $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param array $params
     * @return mixed
     */
    public function aroundGetPagerUrl($subject, callable $proceed, $params = [])
    {
        if ($subject->getNameInLayout() != 'product_list_toolbar_pager') {
            return $proceed($params);
        }
        $urlParams = [];
        $urlParams['_current'] = true;
        $urlParams['_escape'] = false;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;
        return $this->urlBuilder->getUrl('*/*/*', $urlParams);
    }
}
