<?php

namespace Connectif\Integration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;


class ConfigHelper extends AbstractHelper
{

    protected $_storeManager;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
    }

    /*
     * @return bool
     */
    public function isEnabled($scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getCurrentStoreConfig($scope)['is_active'];
    }

     /*
     * @return bool
     */
    public function isMultiAccountEnabled($scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(
            'connectif/config/is_active_multi_currency',
            $scope
        );
    }

     /**
     * Return multi-accounts configuration
     * @param ScopeInterface $scope
     * @return array
     */
    public function getMultiAccounts($scope = ScopeInterface::SCOPE_STORE)
    {
        $accounts = $this->scopeConfig->getValue(
            'connectif/config/multi_account_config',
            $scope
        );

        $accountsConfiguration = $this->getUnserializedConfigValue($accounts);
        
        return $accountsConfiguration;
    }

    /*
     * @return string
     */
    public function getClientId($scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getCurrentStoreConfig($scope)['client_id'];
    }

    /*
     * @return string
     */
    public function getSecretKey($scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getCurrentStoreConfig($scope)['secret_key'];
    }

    /**
     * Return banner configuration
     * @param string $blockName is the banner block name
     * @param ScopeInterface $scope
     * @return array
     */
    public function getBanners($blockName, $scope = ScopeInterface::SCOPE_STORE)
    {
        $banners = $this->scopeConfig->getValue(
            'connectif/banners/banner_config',
            $scope
        );

        $bannerConfiguration = $this->getUnserializedConfigValue($banners);
        $bannerIds = array();

        if (is_array($bannerConfiguration)) {
            foreach ($bannerConfiguration as $struct) {
                $field = (array)$struct;
                if (
                    array_key_exists('block_name', $field)
                    && $field['block_name'] === $blockName
                    && array_key_exists('banner_id', $field)
                ) {
                    array_push($bannerIds, $field['banner_id']);
                }
            }
        }

        return $bannerIds;
    }

    /**
     * Get unserialized config value
     * temporarily solution to get unserialized config value
     * should be deprecated in 2.3.x
     * valid from 2.0.x to 2.3.x
     *
     * @param $value
     * @return mixed
     */
    private function getUnserializedConfigValue($value)
    {
        if (empty($value)) {
            return false;
        }

        if ($this->isSerialized($value)) {
            $unserializer = ObjectManager::getInstance()->get(\Magento\Framework\Unserialize\Unserialize::class);
        } else {
            $unserializer = ObjectManager::getInstance()->get(\Magento\Framework\Serialize\Serializer\Json::class);
        }

        return $unserializer->unserialize($value);
    }

    /**
     * Get serialized config value
     * @param $value
     * @return mixed
     */
    public function getSerializedConfigValue($value)
    {
        if (empty($value)) {
            return false;
        }

        $serializer = ObjectManager::getInstance()->get(\Magento\Framework\Serialize\Serializer\Json::class);

        return $serializer->serialize($value);
    }

    /**
     * Check if value is a serialized string
     *
     * @param string $value
     * @return boolean
     */
    private function isSerialized($value)
    {
        return (boolean)preg_match('/^((s|i|d|b|a|O|C):|N;)/', $value);
    }

    /**
     * Returns current store config for store-view and currency selected if multi-account is enabled
     * @return $storeConfig
     */
    public function getCurrentStoreConfig($scope = ScopeInterface::SCOPE_STORE)
    {
        $storeConfig = array();
        $storeConfig['is_active'] = false;
        $storeConfig['client_id'] = '';
        $storeConfig['secret_key'] = '';

        $currentStore = $this->_storeManager->getStore();

        $isMultiAccountEnabled = $this->isMultiAccountEnabled($scope);
        if ($isMultiAccountEnabled) {
            $multiAccountFields = $this->getMultiAccounts($scope);
            if ($multiAccountFields) {
                if (is_array($multiAccountFields)) {

                    $currencyId = $currentStore->getCurrentCurrency()->getCode();
                    $currentStoreConfig = null;
                    foreach ($multiAccountFields as $struct) {
                        $field = (array) $struct;
                        if ($currencyId == $field['currency_id'] && $field['is_active'] == 1) {
                            $currentStoreConfig = $struct;
                            break;
                        }
                    }
                    if ($currentStoreConfig && array_key_exists('is_active', $currentStoreConfig)) {
                        $storeConfig['is_active'] = $currentStoreConfig['is_active'];
                        $storeConfig['client_id'] = $currentStoreConfig['client_id'];
                        $storeConfig['secret_key'] = $currentStoreConfig['secret_key'];
                    }
                } else {
                    return $storeConfig;
                }
            }
        } else {
            $storeConfig['is_active'] = $this->scopeConfig->isSetFlag(
                'connectif/config/is_active',
                $scope
            );
            $storeConfig['client_id'] = $this->scopeConfig->getValue(
                'connectif/config/client_id',
                $scope
            );
            $storeConfig['secret_key'] = $this->scopeConfig->getValue(
                'connectif/config/secret_key',
                $scope
            );
        }
        return $storeConfig;
    }
    
}
