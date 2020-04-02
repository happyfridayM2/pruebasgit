<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\UrlRewrite\Model\StoreSwitcher;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Class RewriteUrl
 * @package Amasty\Shopby\Plugin\UrlRewrite\Model\StoreSwitcher
 */
class RewriteUrl
{
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RequestFactory
     */
    private $requestFactory;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request
     */
    private $request;

    public function __construct(\Magento\Framework\HTTP\PhpEnvironment\RequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    /**
     * @param mixed $subject
     * @param StoreInterface $fromStore
     * @param StoreInterface $targetStore
     * @param string $redirectUrl
     * @return array
     */
    public function beforeSwitch($subject, StoreInterface $fromStore, StoreInterface $targetStore, $redirectUrl)
    {
        $this->request = $this->requestFactory->create();
        $this->request->setUri($redirectUrl);
        return [$fromStore, $targetStore, $redirectUrl];
    }

    /**
     * @param mixed $subject
     * @param string $result
     * @return string
     */
    public function afterSwitch($subject, $result)
    {
        $requestUri = $this->request->getUri()->toString();
        $queryString = $this->request->getUri()->getQuery();
        if ($requestUri != $result && $queryString) {
            $result .= '?' . $queryString;
        }

        return $result;
    }
}
