<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Plugin\Shopby\Controller;

use Magento\Framework\App\RequestInterface;

/**
 * Class Router
 * @package Amasty\ShopbySeo\Plugin\Shopby\Controller
 */
class Router
{
    /**
     * @var \Amasty\ShopbySeo\Helper\Url
     */
    private $urlHelper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private $identifier;

    public function __construct(\Amasty\ShopbySeo\Helper\Url $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param $subject
     * @param RequestInterface $request
     * @param $identifier
     * @return array
     */
    public function beforeCheckMatchExpressions($subject, RequestInterface $request, $identifier)
    {
        $this->request = $request;
        $this->identifier = $identifier;
        return [$request, $identifier];
    }

    /**
     * @param $subject
     * @param $result
     * @return bool
     */
    public function afterCheckMatchExpressions($subject, $result)
    {
        if ($this->urlHelper->isSeoUrlEnabled()) {
            $hasParams = $this->request->getMetaData(\Amasty\ShopbySeo\Helper\Data::HAS_PARSED_PARAMS)
                && !$this->request->getMetaData(\Amasty\ShopbySeo\Helper\Data::HAS_ROUTE_PARAMS);
            return $result || $hasParams;
        }
        return $result;
    }
}
