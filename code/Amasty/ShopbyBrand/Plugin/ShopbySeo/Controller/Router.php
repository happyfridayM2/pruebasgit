<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Plugin\ShopbySeo\Controller;

use Magento\Framework\App\RequestInterface;

/**
 * Class Router
 *
 * @package Amasty\ShopbyBrand\Plugin\ShopbySeo\Controller
 */
class Router
{
    const SINGLE_PARAM = 1;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $brandHelper;

    public function __construct(\Amasty\ShopbyBrand\Helper\Data $brandHelper)
    {
        $this->brandHelper = $brandHelper;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @param $identifier
     * @param $params
     */
    public function aroundModifyRequest(
        $subject,
        callable $proceed,
        RequestInterface $request,
        $identifier,
        $params
    ) {
        $brandAttributeCode = $this->brandHelper->getBrandAttributeCode();
        $brandUrlKey = $this->brandHelper->getBrandUrlKey();
        if (count($params) == self::SINGLE_PARAM
            && isset($params[$brandAttributeCode])
            && trim($identifier, '/') == $brandUrlKey
        ) {
            return $subject;
        }

        return $proceed($request, $identifier, $params);
    }
}
