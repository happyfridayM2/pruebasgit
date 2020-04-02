<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class General
 *
 * @package Magmodules\AlternateHreflang\Helper
 */
class General extends AbstractHelper
{

    const ALTERNATE_ENABLE = 'magmodules_alternate/general/enable';
    const ALTERNATE_HOMEPAGE = 'magmodules_alternate/configuration/homepage';
    const ALTERNATE_PRODUCT = 'magmodules_alternate/configuration/product';
    const ALTERNATE_CATEGORY = 'magmodules_alternate/configuration/category';
    const ALTERNATE_CMS = 'magmodules_alternate/configuration/cms';
    const ALTERNATE_DEBUG = 'magmodules_alternate/configuration/debug';
    const ALTERNATE_CANONICAL = 'magmodules_alternate/configuration/canonical';
    const ALTERNATE_STORES = 'magmodules_alternate/targeting/stores';
    const MODULE_CODE = 'Magmodules_AlternateHreflang';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ModuleListInterface
     */
    private $moduleList;
    /**
     * @var ProductMetadataInterface
     */
    private $metadata;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * General constructor.
     *
     * @param Context                  $context
     * @param StoreManagerInterface    $storeManager
     * @param ModuleListInterface      $moduleList
     * @param ProductMetadataInterface $metadata
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $metadata
    ) {
        $this->storeManager = $storeManager;
        $this->moduleList = $moduleList;
        $this->metadata = $metadata;
        $this->urlInterface = $context->getUrlBuilder();
        parent::__construct($context);
    }

    /**
     * General enable check
     *
     * @param $type
     *
     * @return bool
     */
    public function getEnabled($type)
    {
        $enabled = $this->getStoreValue(self::ALTERNATE_ENABLE);
        if (!$enabled) {
            return false;
        }

        if ($type == 'homepage') {
            return $this->getStoreValue(self::ALTERNATE_HOMEPAGE);
        }
        if ($type == 'product') {
            return $this->getStoreValue(self::ALTERNATE_PRODUCT);
        }
        if ($type == 'category') {
            return $this->getStoreValue(self::ALTERNATE_CATEGORY);
        }
        if ($type == 'cms') {
            return $this->getStoreValue(self::ALTERNATE_CMS);
        }

        return false;
    }

    /**
     * Get Configuration data.
     *
     * @param      $path
     * @param      $scope
     * @param null $storeId
     *
     * @return mixed
     */
    public function getStoreValue($path, $storeId = null, $scope = null)
    {
        if (empty($scope)) {
            $scope = ScopeInterface::SCOPE_STORE;
        }

        return $this->scopeConfig->getValue($path, $scope, $storeId);
    }

    /**
     * Config check for debug
     *
     * @return mixed
     */
    public function getAlternateDebug()
    {
        $enabled = $this->getStoreValue(self::ALTERNATE_ENABLE);
        if (!$enabled) {
            return false;
        }

        return $this->getStoreValue(self::ALTERNATE_DEBUG);
    }

    /**
     * Gets config value for "use canonical"
     *
     * @return mixed
     */
    public function getCanonicalEnabled()
    {
        return $this->getStoreValue(self::ALTERNATE_CANONICAL);
    }

    /**
     * Returns all stores from the same group
     *
     * @param $storeId
     *
     * @return array|bool
     */
    public function getTargetData($storeId)
    {
        $targetData = [];
        $data = $this->getStoreValueArray(self::ALTERNATE_STORES);
        if ($data && is_array($data)) {
            foreach ($data as $key => $value) {
                $targetData[$value['group_id']][] = $value;
                if ($value['store_id'] == $storeId) {
                    $targetData['group_id'] = $value['group_id'];
                }
            }

            return $targetData;
        }

        return false;
    }

    /**
     * Get Configuration Array data.
     * Pre Magento 2.2.x => Unserialize
     * Magento 2.2.x and up => Json Decode
     *
     * @param      $path
     * @param null $storeId
     * @param null $scope
     *
     * @return array|mixed
     */
    public function getStoreValueArray($path, $storeId = null, $scope = null)
    {
        $value = $this->getStoreValue($path, $storeId, $scope);
        $result = json_decode($value, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            if (is_array($result)) {
                return $result;
            }
            return [];
        }

        $value = @unserialize($value);
        if (is_array($value)) {
            return $value;
        }

        return [];
    }

    /**
     * Returns current StoreId
     *
     * @return int
     */
    public function getCurrentStore()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    public function getStores()
    {
        return $this->storeManager->getStores();
    }

    /**
     * Returns the current url
     *
     * @param string $clean
     *
     * @return string
     */
    public function getCurrentUrl($clean = '')
    {
        $currentUrl = $this->urlInterface->getCurrentUrl();
        if ($clean) {
            $currentUrl = strtok($currentUrl, '?');
        }

        return $currentUrl;
    }

    /**
     * Gets base url by storeId
     *
     * @param string $storeId
     *
     * @return mixed
     */
    public function getBaseUrlStore($storeId = '')
    {
        return $this->storeManager->getStore($storeId)->getBaseUrl();
    }

    /**
     * Returns current version of the extension
     *
     * @return mixed
     */
    public function getExtensionVersion()
    {
        $moduleInfo = $this->moduleList->getOne(self::MODULE_CODE);

        return $moduleInfo['setup_version'];
    }

    /**
     * Returns current version of Magento
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->metadata->getVersion();
    }

    /**
     * @param $storeId
     *
     * @return int
     */
    public function getWebsiteIdFromStoreId($storeId)
    {
        return $this->storeManager->getStore($storeId)->getWebsiteId();
    }
}
