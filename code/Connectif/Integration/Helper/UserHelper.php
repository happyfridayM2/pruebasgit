<?php

namespace Connectif\Integration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadata;
use Connectif\Integration\Helper\SystemHelper;

class UserHelper extends AbstractHelper
{

    protected $_systemHelper;
    protected $_productMetadata;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        SystemHelper $systemHelper,
        ProductMetadata $productMetadata
    )
    {
        parent::__construct($context);
        $this->_systemHelper = $systemHelper;
        $this->_productMetadata = $productMetadata;
    }

    public function activateAccount(
        $account,
        $connectifModuleVersion,
        $apiEndpoint,
        $forceServiceWorkerOverride,
        $serviceWorkerUrl,
        $optOutUrl,
        $optInUrl
    ) {
        $phpVersion = (string) phpversion();
        $magentoVersion = $this->_productMetadata->getVersion();
        // Module is activating
        $data = array(
            'phpVersion' => $phpVersion,
            'magentoVersion' => $magentoVersion,
            'moduleVersion' => $connectifModuleVersion,
            'serviceWorkerUrl' => $serviceWorkerUrl,
            'forceServiceWorkerOverride' => $forceServiceWorkerOverride || false,
            'optOutUrl' => $optOutUrl,
            'optInUrl' => $optInUrl,
            'checksum' => $account->checksum,
            'clientId' => $account->client_id,
            'language' => $account->lang_name,
            'currency' => $account->currency_name,
        );
        $actionPath = '/integration-type/magento/' . $account->client_id . '/auto-config';
        $activationResponse = $this->_systemHelper->doPostRequestCn($actionPath, $data, $apiEndpoint);
        return $activationResponse;
    }

}