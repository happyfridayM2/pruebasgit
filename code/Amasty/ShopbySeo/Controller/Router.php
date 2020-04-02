<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Controller;

use Amasty\ShopbySeo\Helper\Data;
use Amasty\ShopbySeo\Helper\Url;
use Amasty\ShopbySeo\Helper\UrlParser;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Router
 *
 * @package Amasty\ShopbySeo\Controller
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var bool
     */
    private $isSuffixRemoved = false;

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * @var UrlParser
     */
    private $urlParser;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        UrlParser $urlParser,
        Url $urlHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Data $helper
    ) {
        $this->urlHelper = $urlHelper;
        $this->urlParser = $urlParser;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function match(RequestInterface $request)
    {
        if ($request->getMetaData(Data::SKIP_REQUEST_FLAG) || $this->skipRequest($request)) {
            $request->setMetaData(Data::SKIP_REQUEST_FLAG, true);

            return false;
        }

        $this->initRequestMetaData($request);

        $pathInfo = $request->getPathInfo();
        $identifier = $this->removeSuffix($pathInfo, $request);
        $this->isSuffixRemoved = $pathInfo !== $identifier;

        list($seoPart, $identifier) = $this->getSeoPartAndIdentifier($identifier, $request);
        if ($request->getMetaData(Data::SKIP_REQUEST_FLAG)) {
            return false;
        }

        $params = $this->urlParser->parseSeoPart($seoPart);
        $this->checkSeoParams($request, $params);
        if (!empty($params)) {
            $this->modifyRequest($request, $identifier, $params);
        }

        $request->setMetaData(Data::SKIP_REQUEST_FLAG, true);

        return false;
    }

    /**
     * @param $identifier
     * @param $request
     * @return array|bool
     */
    private function getSeoPartAndIdentifier($identifier, $request)
    {
        $seoPart = '';
        $filterWord = $this->helper->getFilterWord();
        if ($filterWord) {
            if (strpos($identifier, '/' . $filterWord . '/') !== false) {
                $filterWordPosition = strpos($identifier, '/' . $filterWord . '/');
                $seoPart = substr(
                    $identifier,
                    $filterWordPosition + strlen('/' . $filterWord . '/')
                );
                $identifier = substr($identifier, 0, $filterWordPosition);
            } else {
                $this->checkSeoParams($request);
                $request->setMetaData(Data::SKIP_REQUEST_FLAG, true);
            }
        } else {
            $lastSlashPosition = strrpos($identifier, "/");
            $lastSlashPosition = ($lastSlashPosition === false) ? 0 : $lastSlashPosition;
            $seoPart = substr($identifier, $lastSlashPosition + 1);
            $identifier = substr($identifier, 0, $lastSlashPosition);
        }

        return [$seoPart, $identifier];
    }

    /**
     * @param $identifier
     * @param $request
     *
     * @return string
     */
    private function removeSuffix($identifier, $request)
    {
        if (trim($identifier, '/') && $this->getSeoSuffix()) {
            $suffixPosition = strrpos($identifier, $this->getSeoSuffix());

            if ($suffixPosition !== false
                && ($suffixPosition == strlen($identifier) - strlen($this->getSeoSuffix()))
            ) {
                $identifier = substr($identifier, 0, $suffixPosition);
                if (!$this->urlHelper->getAddSuffixSettingValue() && !$request->isAjax()) {
                    $request->setMetaData(Data::SEO_REDIRECT_MISSED_SUFFIX_FLAG, true);
                }
            } elseif ($this->urlHelper->getAddSuffixSettingValue() && !$request->isAjax()) {
                $request->setMetaData(Data::SEO_REDIRECT_MISSED_SUFFIX_FLAG, true);
            }
        }

        return $identifier;
    }

    /**
     * @param RequestInterface $request
     * @param $identifier
     * @param array $params
     *
     * @return $this
     */
    public function modifyRequest(RequestInterface $request, $identifier, $params = [])
    {
        if (strlen($identifier)) {
            $request->setMetaData(Data::HAS_ROUTE_PARAMS, true);
            if ($this->isSuffixRemoved) {
                $identifier .= $this->getSeoSuffix();
            }

            $request->setPathInfo($identifier);
        }

        $request->setParams($params);
        $request->setMetaData(Data::HAS_PARSED_PARAMS, true);

        return $this;
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function skipRequest(RequestInterface $request)
    {
        return !$this->helper->isAllowedRequest($request, true);
    }

    /**
     * @return string
     */
    public function getSeoSuffix()
    {
        return $this->scopeConfig
            ->getValue('catalog/seo/category_url_suffix', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param RequestInterface $request
     * @param array $parsedParams
     *
     * @return $this
     */
    private function checkSeoParams(RequestInterface $request, array $parsedParams = [])
    {
        $userExtraParams = array_diff_assoc($request->getUserParams(), $parsedParams);
        if ($this->urlParser->checkSeoParams(array_merge((array)$request->getQuery(), $userExtraParams))
            && !$this->isAjax($request)
        ) {
            $request->setMetaData(Data::SEO_REDIRECT_FLAG, true);
        }

        return $this;
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    private function isAjax(RequestInterface $request)
    {
        return $request->isAjax();
    }

    /**
     * @param RequestInterface $request
     *
     * @return $this
     */
    public function initRequestMetaData(RequestInterface $request)
    {
        $request->setMetaData(Data::SEO_REDIRECT_FLAG, false);
        $request->setMetaData(Data::SEO_REDIRECT_MISSED_SUFFIX_FLAG, false);
        $request->setMetaData(Data::HAS_PARSED_PARAMS, false);

        return $this;
    }
}
