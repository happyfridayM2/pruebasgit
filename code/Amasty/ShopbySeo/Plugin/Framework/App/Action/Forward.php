<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Plugin\Framework\App\Action;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Forward as ForwardAction;

/**
 * Class Forward
 * @package Amasty\ShopbySeo\Plugin\Framework\App\Action
 */
class Forward
{
    /**
     * @var \Amasty\ShopbySeo\Helper\Url
     */
    private $urlHelper;

    /**
     * @var array
     */
    private $suffixModules = ['catalog', 'amshopby', 'ambrand'];

    public function __construct(\Amasty\ShopbySeo\Helper\Url $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ForwardAction $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function aroundDispatch(ForwardAction $subject, callable $proceed, RequestInterface $request)
    {
        /**
         * @TODO does not work for catalog pages with filters
         */
        if ($request->getMetaData(\Amasty\ShopbySeo\Helper\Data::SEO_REDIRECT_FLAG) && $request->getModuleName()) {
            $request->setDispatched(true);
            return $subject->getResponse();
        } elseif ($request->getMetaData(\Amasty\ShopbySeo\Helper\Data::SEO_REDIRECT_MISSED_SUFFIX_FLAG)
            && $this->urlHelper->isAddSuffixToShopby()
            && in_array($request->getModuleName(), $this->suffixModules)
        ) {
            $request->setMetaData(\Amasty\ShopbySeo\Helper\Data::SEO_REDIRECT_FLAG, true);
            if ($request->getModuleName()) {
                $request->setDispatched(true);
                return $subject->getResponse();
            }
        }

        return $proceed($request);
    }
}
