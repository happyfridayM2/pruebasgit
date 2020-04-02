<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Api\UrlBuilderInterface;
use Amasty\ShopbyBase\Api\UrlBuilder\AdapterInterface;
use Amasty\ShopbyBase\Api\UrlModifierInterface;

/**
 * UrlBuilder
 */
class UrlBuilder implements UrlBuilderInterface
{
    const DEFAULT_ORDER = 100;

    /**
     * @var AdapterInterface[]
     */
    private $urlAdapters = [];

    /**
     * @var UrlModifierInterface[]
     */
    private $urlModifiers = [];

    public function __construct(
        $urlAdapters = [],
        $urlModifiers = []
    ) {
        $this->initAdapters($urlAdapters);
        $this->initModifiers($urlModifiers);
    }

    /**
     * @param null $routePath
     * @param null $routeParams
     * @return string|null
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        $url = null;
        foreach ($this->urlAdapters as $adapter) {
            if ($url = $adapter->getUrl($routePath, $routeParams)) {
                break;
            }
        }

        foreach ($this->urlModifiers as $modifier) {
            $url = $modifier->modifyUrl($url);
        }

        return $url;
    }

    /**
     * @param bool $modified = true
     * @return string|null
     */
    public function getCurrentUrl($modified = true)
    {
        $url = null;
        foreach ($this->urlAdapters as $adapter) {
            if (method_exists($adapter, 'getCurrentUrl')) {
                $url = $adapter->getCurrentUrl();
                break;
            }
        }

        if ($modified) {
            foreach ($this->urlModifiers as $modifier) {
                $url = $modifier->modifyUrl($url);
            }
        }

        return $url;
    }

    /**
     * @param array $params
     * @return string|null
     */
    public function getBaseUrl($params = [])
    {
        $url = null;
        foreach ($this->urlAdapters as $adapter) {
            if (method_exists($adapter, 'getBaseUrl')) {
                $url = $adapter->getBaseUrl($params);
                break;
            }
        }

        return $url;
    }

    /**
     * @param array $urlAdapters
     * @return $this
     */
    private function initAdapters(array $urlAdapters = [])
    {
        foreach ($urlAdapters as $urlAdapter) {
            if (isset($urlAdapter['adapter'])
                && ($urlAdapter['adapter'] instanceof AdapterInterface)
            ) {
                $order = isset($urlAdapter['sort_order']) ? $urlAdapter['sort_order'] : self::DEFAULT_ORDER;
                $this->urlAdapters[$order] = $urlAdapter['adapter'];
            }
        }
        ksort($this->urlAdapters, SORT_NUMERIC);
        return $this;
    }

    /**
     * @param array $urlModifiers
     * @return $this
     */
    private function initModifiers(array $urlModifiers = [])
    {
        foreach ($urlModifiers as $urlModifier) {
            if (isset($urlModifier['adapter'])
                && ($urlModifier['adapter'] instanceof UrlModifierInterface)
            ) {
                $order = isset($urlModifier['sort_order']) ? $urlModifier['sort_order'] : self::DEFAULT_ORDER;
                $this->urlModifiers[$order] = $urlModifier['adapter'];
            }
        }
        ksort($this->urlModifiers, SORT_NUMERIC);
        return $this;
    }
}
