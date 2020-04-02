<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\UrlBuilder;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;

/**
 * Class CategoryAdapter
 * @package Amasty\Shopby\Model\UrlBuilder
 */
class CategoryAdapter implements \Amasty\ShopbyBase\Api\UrlBuilder\AdapterInterface
{
    const SELF_ROUTE_PATH = 'catalog/category/view';

    /**
     * @var \Magento\Framework\Url
     */
    private $urlBuilder;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Framework\Url $urlBuilder,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->urlFinder = $urlFinder;
        $this->storeManager = $storeManager;
    }

    /**
     * @param null $routePath
     * @param null $routeParams
     * @return string|null
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        $routePath = trim($routePath, '/');
        if ($routePath == self::SELF_ROUTE_PATH && isset($routeParams['id'])) {
            try {
                $rewrite = $this->urlFinder->findOneByData([
                    UrlRewrite::ENTITY_ID => (int)$routeParams['id'],
                    UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::STORE_ID => $this->storeManager->getStore()->getId()
                ]);
                if ($rewrite) {
                    if (isset($routeParams['_scope'])) {
                        $this->urlBuilder->setScope($routeParams['_scope']);
                    } else {
                        $this->urlBuilder->setScope(null);
                    }
                    $routeParams['_direct'] = $rewrite->getRequestPath();
                    $routePath = '';
                    return $this->urlBuilder->getUrl($routePath, $routeParams);
                }
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }
        return null;
    }
}
