<?php

namespace Connectif\Integration\Observer\Settings;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ScopeInterface as ScopeInterfaceFramework;
use Magento\Framework\App\Cache\Type\Config as CacheConfig;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Asset\Repository;
use Connectif\Integration\Helper\ConfigHelper;
use Connectif\Integration\Helper\SystemHelper;
use Connectif\Integration\Helper\UserHelper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Update
 * @package Connectif\Integration\Observer
 */
class Update implements ObserverInterface
{
    protected $_userHelper;
    protected $_resourceConfig;
    protected $_cacheTypeList;
    protected $_messageManager;
    protected $_assetRepository;
    protected $_storeManager;

    public function __construct(
       ConfigHelper $configHelper,
       SystemHelper $systemHelper,
       UserHelper $userHelper,
       Config $resourceConfig,
       TypeListInterface $cacheTypeList,
       ManagerInterface $messageManager,
       Repository $assetRepository,
       StoreManagerInterface $storeManager
    ) {
      $this->_configHelper = $configHelper;
      $this->_systemHelper = $systemHelper;
      $this->_userHelper = $userHelper;
      $this->_resourceConfig = $resourceConfig; 
      $this->_cacheTypeList = $cacheTypeList; 
      $this->_messageManager = $messageManager; 
      $this->_assetRepository = $assetRepository; 
      $this->_storeManager = $storeManager;
    }

    public function execute(Observer $observer)
    {
        $appConfig = $this->_systemHelper->getConnectifAppConfig();
        $apiEndpoint = $appConfig->apiProtocol . '://' . $appConfig->apiDomain . ':' . $appConfig->apiPort;
        $connectifModuleVersion = $this->_systemHelper->getModuleVersion();        
        $params = array();
        $params['area'] = 'frontend';
        $serviceWorkerUrl = $this->_assetRepository->getUrlWithParams('Connectif_Integration::service-worker.js', $params);
        $optOutUrl = $this->_storeManager->getStore()->getBaseUrl() . 'connectif/newsletter/optout';
        $optInUrl = $this->_storeManager->getStore()->getBaseUrl() . 'connectif/newsletter/optin';
        
        $isActive = $this->_configHelper->isEnabled(ScopeInterface::SCOPE_STORE);
        $isMultiAccount = $this->_configHelper->isMultiAccountEnabled(ScopeInterface::SCOPE_STORE);

        if ($isActive && !$isMultiAccount) {
            $this->activateSingleAccount(
                $connectifModuleVersion,
                $apiEndpoint,
                false,
                $serviceWorkerUrl,
                $optOutUrl,
                $optInUrl
            );
        } else if ($isMultiAccount) {
            
            $accountsConfig = $this->_configHelper->getMultiAccounts(ScopeInterface::SCOPE_STORE);
            $this->activateMultiAccount(
                $accountsConfig,
                $connectifModuleVersion,
                $apiEndpoint,
                false,
                $serviceWorkerUrl,
                $optOutUrl,
                $optInUrl
            );

        }
        return $this;
    }


    private function activateMultiAccount(
        $multiAccountArray,
        $connectifModuleVersion,
        $apiEndpoint,
        $overrideServiceWorker,
        $serviceWorkerUrl,
        $optOutUrl,
        $optInUrl
    ) {
        if (!is_array($multiAccountArray)) {
            return;
        }

        if ($multiAccountArray && is_array($multiAccountArray) && count($multiAccountArray) !== 0) {

            $duplicatedCurrencies = $this->getDuplicatedCurrencies($multiAccountArray);

            if ($duplicatedCurrencies && is_array($duplicatedCurrencies) && count($duplicatedCurrencies) > 0) {
                foreach ($multiAccountArray as $key => $multiAccountArrayRow) {
                    if (!is_array($multiAccountArrayRow) || !$multiAccountArrayRow['is_active']) {
                        continue;
                    }
                    if (array_search($multiAccountArrayRow['currency_id'], $duplicatedCurrencies) !== false) {
                        $multiAccountArray[$key]['is_active'] = 0;
                    }
                }
                $this->_resourceConfig->saveConfig(
                    'connectif/config/multi_account_config',
                    $this->_configHelper->getSerializedConfigValue($multiAccountArray),
                    ScopeInterfaceFramework::SCOPE_DEFAULT,
                    0
                );
                $this->_cacheTypeList->cleanType(CacheConfig::TYPE_IDENTIFIER);
                $this->_messageManager->addError(__("Module Activation Failed, duplicated currency found for different accounts."));
            }
            foreach ($multiAccountArray as $key => $multiAccountArrayRow) {
                if (!is_array($multiAccountArrayRow) || !$multiAccountArrayRow['is_active']) {
                    continue;
                }
                $clientId = $multiAccountArrayRow['client_id'];
                $apiKey = $multiAccountArrayRow['secret_key'];
                //$currencyId = $multiAccountArrayRow['currency_id'];
                $checksum = hash_hmac('sha1', $clientId, $apiKey);

                // TODO send currency_name
                $account = (object) array(
                    'client_id' => $clientId,
                    'checksum' => $checksum,
                    'lang_name' => '',
                    'currency_name' => '',
                );

                $activationResponse = $this->_userHelper->activateAccount(
                    $account,
                    $connectifModuleVersion,
                    $apiEndpoint,
                    $overrideServiceWorker,
                    $serviceWorkerUrl,
                    $optOutUrl,
                    $optInUrl
                );

                if (!$activationResponse || !$activationResponse->isValid) {
                    $multiAccountArray[$key]['is_active'] = 0;
                    $this->_resourceConfig->saveConfig(
                        'connectif/config/multi_account_config',
                        $this->_configHelper->getSerializedConfigValue($multiAccountArray),
                        ScopeInterfaceFramework::SCOPE_DEFAULT,
                        0
                    );
                    $this->_cacheTypeList->cleanType(CacheConfig::TYPE_IDENTIFIER);
                    $this->_messageManager->addError(__(
                        'Module Activation Failed, check your Client ID and Secret Key of account "'
                        . 'Client ID: '
                        . $clientId
                        . ' Secret Key: '
                        . $apiKey
                        . '" and try again.'
                    ));
                }
            }
        } else {
            $this->_messageManager->addError(__('Module Activation Failed, configure at least one account.'));
        }
    }

    private function getDuplicatedCurrencies($multiAccountArray)
    {
        $currenciesUsed = array();
        $duplicatedCurrencies = array();
        foreach ($multiAccountArray as $multiAccountArrayRow) {
            if (!is_array($multiAccountArrayRow) || !$multiAccountArrayRow['is_active']) {
                continue;
            }
            if (array_search($multiAccountArrayRow['currency_id'], $currenciesUsed) !== false) {
                array_push($duplicatedCurrencies, $multiAccountArrayRow['currency_id']);
            } else {
                array_push($currenciesUsed, $multiAccountArrayRow['currency_id']);
            }
        }
        return $duplicatedCurrencies;
    }

    private function activateSingleAccount(
        $connectifModuleVersion,
        $apiEndpoint,
        $overrideServiceWorker,
        $serviceWorkerUrl,
        $optOutUrl,
        $optInUrl
    ) { 
        $clientId = $this->_configHelper->getClientId(ScopeInterface::SCOPE_STORE);
        $apiKey = $this->_configHelper->getSecretKey(ScopeInterface::SCOPE_STORE);
        $checksum = hash_hmac('sha1', $clientId, $apiKey);

        $account = (object) array(
            'client_id' => $clientId,
            'checksum' => $checksum,
            'lang_name' => '',
            'currency_name' => '',
        );

        $activationResponse = $this->_userHelper->activateAccount(
            $account,
            $connectifModuleVersion,
            $apiEndpoint,
            $overrideServiceWorker,
            $serviceWorkerUrl,
            $optOutUrl,
            $optInUrl
        );

        if (!$activationResponse || !$activationResponse->isValid) {
            $this->_resourceConfig->saveConfig(
                'connectif/config/is_active',
                0,
                ScopeInterfaceFramework::SCOPE_DEFAULT,
                0
            );
            $this->_cacheTypeList->cleanType(CacheConfig::TYPE_IDENTIFIER);
            $this->_messageManager->addError(__("Module Activation Failed, check your Client ID and Secret Key and try again."));
        }
    }

}
