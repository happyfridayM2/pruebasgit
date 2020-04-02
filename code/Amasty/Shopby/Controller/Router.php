<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    private $actionFactory;

    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Amasty\Shopby\Helper\Data $helper
    ) {
        $this->actionFactory = $actionFactory;
        $this->helper = $helper;
    }

    /**
     * @param RequestInterface $request
     * @return bool|\Magento\Framework\App\ActionInterface
     */
    public function match(RequestInterface $request)
    {
        if (!$this->helper->isAllProductsEnabled()) {
            return false;
        }

        $identifier = trim($request->getPathInfo(), '/');

        $seoSuffix = $this->helper->getCatalogSeoSuffix();
        if ($seoSuffix) {
            $suffixPosition = strpos($identifier, $seoSuffix);
            if ($suffixPosition !== false) {
                $identifier = substr($identifier, 0, $suffixPosition);
            }
        }

        if ($this->checkMatchExpressions($request, $identifier)) {
            $request->setModuleName('amshopby')
                ->setControllerName('index')
                ->setActionName('index')
                ->setAlias(
                    \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS,
                    $identifier
                );

            return $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class);
        }

        return false;
    }

    /**
     * @param RequestInterface $request
     * @param string $identifier
     * @return bool
     */
    public function checkMatchExpressions(RequestInterface $request, $identifier)
    {
        return $identifier == $this->helper->getAllProductsUrlKey();
    }
}
