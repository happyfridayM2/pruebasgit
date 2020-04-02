<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Module\Manager;

/**
 * Class Router
 *
 * @package Amasty\ShopbyBrand\Controller
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    private $actionFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */

    private $response;

    /**
     * @var  Manager
     */
    private $moduleManager;

    /**
     * @var  \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var string
     */
    private $brandCode;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $brandHelper;

    /**
     * @var \Amasty\ShopbyBase\Helper\PermissionHelper
     */
    private $permissionHelper;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Module\Manager $moduleManager,
        \Amasty\ShopbyBrand\Helper\Data $brandHelper,
        \Amasty\ShopbyBase\Helper\PermissionHelper $permissionHelper
    ) {
        $this->actionFactory = $actionFactory;
        $this->response = $response;
        $this->moduleManager = $moduleManager;
        $this->registry = $registry;
        $this->brandCode = $brandHelper->getBrandAttributeCode();
        $this->urlBuilder = $urlBuilder;
        $this->brandHelper = $brandHelper;
        $this->permissionHelper = $permissionHelper;
    }

    /**
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ActionInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function match(RequestInterface $request)
    {
        $result = null;

        $identifier = $this->getPath($request);
        $brandParams = $this->matchBrandParams($identifier);

        if (!empty($brandParams) &&
            $this->isExistUrlKey($identifier) &&
            $this->permissionHelper->checkPermissions()
        ) {
            /*
             * There is no redirect to single brand, because this extension doesn't support
             * multiple filter values. It means, that situation when someone will request two brands is impossible
             */
            $brandValue = $this->getBrandValue($brandParams);
            if ($this->checkMultibrand($brandValue)) {
                $request->setParams($this->retrieveFirstBrand($brandValue));
                $result = $this->redirectToSingleBrand($request);
            } else {
                $this->requestToBrandPage($request, $identifier, $brandParams);
                $result = $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class);
            }
        }

        return $result;
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    private function isExistUrlKey($identifier)
    {
        $brandUrlKey = $this->brandHelper->getBrandUrlKey();

        return $brandUrlKey ?
            strpos($identifier, $brandUrlKey) !== false :
            true;
    }

    /**
     * @param $request
     * @param $identifier
     * @param $brandParams
     */
    private function requestToBrandPage($request, $identifier, $brandParams)
    {
        $request->setModuleName('ambrand')
            ->setControllerName('index')
            ->setActionName('index');
        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
        $params = array_merge($request->getParams(), $brandParams);
        $request->setParams($params);
    }

    /**
     * @param array $params
     *
     * @return string|null
     */
    private function getBrandValue($params)
    {
        $brandValue = null;
        if ($this->brandCode && isset($params[$this->brandCode])) {
            $brandValue = $params[$this->brandCode];
            if (is_array($brandValue)) {
                $brandValue = array_shift($brandValue);
            }
        }

        return $brandValue;
    }

    /**
     * @param string $brandValue
     *
     * @return bool|int
     */
    private function checkMultibrand($brandValue)
    {
        return strrpos($brandValue, ',');
    }

    /**
     * @param string $brandValue
     *
     * @return array
     */
    private function retrieveFirstBrand($brandValue)
    {
        $brandValue = substr(
            $brandValue,
            $this->checkMultibrand($brandValue) + 1
        );

        return [
            $this->brandCode => $brandValue
        ];
    }

    /**
     * If this page is brand/brand1-brand2-... redirect to brand/brand1
     */
    private function redirectToSingleBrand(RequestInterface $request)
    {
        $route = sprintf(
            '%s/%s/%s',
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName()
        );
        $url = $this->urlBuilder->getUrl($route, ['_query' => $request->getParams()]);
        $this->response->setRedirect($url, \Zend\Http\Response::STATUS_CODE_301);
        $request->setDispatched(true);
        return $this->actionFactory->create(\Magento\Framework\App\Action\Redirect::class);
    }

    /**
     * @param RequestInterface $request
     *
     * @return string
     */
    private function getPath(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        $suffix = $this->brandHelper->getSuffix();
        if (!empty($suffix) && strpos($identifier, $suffix) !== false) {
            $suffixPosition = strrpos($identifier, $suffix);
            if ($suffixPosition !== false && $suffixPosition == strlen($identifier) - strlen($suffix)) {
                $identifier = substr($identifier, 0, $suffixPosition);
            }
        }

        return $identifier;
    }

    /**
     * @param string $identifier
     *
     * @return array
     */
    public function matchBrandParams($identifier)
    {
        $identifier = $this->cutBrandIdentifier($identifier);
        $aliases = $this->brandHelper->getBrandAliases();

        foreach ($aliases as $optionId => $alias) {
            if (!strcasecmp($alias, $identifier)) {
                return [$this->brandHelper->getBrandAttributeCode() => $optionId];
            }
        }

        return [];
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    private function cutBrandIdentifier($identifier)
    {
        $brandPageUrlKey = $this->brandHelper->getBrandUrlKey();
        $identifier = trim($identifier, '/');

        if (!empty($brandPageUrlKey) && strpos($identifier, $brandPageUrlKey . '/') === 0) {
            $identifier = ltrim(substr($identifier, strlen($brandPageUrlKey . '/')), '/');
        }

        return $identifier;
    }
}
