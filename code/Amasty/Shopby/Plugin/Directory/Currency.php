<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Directory;

use \Magento\Framework\App\ActionInterface;

/**
 * Class Currency
 * @package Amasty\Shopby\Plugin\Directory
 */
class Currency
{
    /**
     * @var \Amasty\ShopbyBase\Api\UrlBuilderInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    private $encoder;

    public function __construct(
        \Amasty\ShopbyBase\Api\UrlBuilderInterface $urlBuilder,
        \Magento\Framework\Url\EncoderInterface $encoder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->encoder = $encoder;
    }

    /**
     * @param $subject
     * @param \Closure $closure
     * @param $code
     * @return false|string
     */
    public function aroundGetSwitchCurrencyPostData($subject, \Closure $closure, $code)
    {
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = ['_' => null, 'shopbyAjax' => null];
        $currentUrl = $this->urlBuilder->getUrl('*/*/*', $params);

        $url = $subject->escapeUrl($subject->getSwitchUrl());

        $data[ActionInterface::PARAM_NAME_URL_ENCODED] = $this->encoder->encode($currentUrl);

        return json_encode(['action' => $url, 'data' => ['currency' => $code]]);
    }
}
