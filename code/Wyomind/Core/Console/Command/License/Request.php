<?php

/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Core\Console\Command\License;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\RuntimeException;

class Request extends Command
{
    /**
     * @var \Magento\Framework\Module\ModuleListFactory
     */
    public $state = null;
    /**
     * @var \Wyomind\Core\Helper\DataFactory|null
     */
    public $configHelperFactory = null;
    /**
     * @var \Wyomind\Core\Helper\LicenseFactory|null
     */
    public $licenseHelperFactory = null;
    /**
     * @var \Wyomind\Core\Model\ResourceModel\ConfigFactory|null
     */
    public $configFactory = null;

    /**
     * Request constructor.
     * @param \Magento\Framework\App\State $state
     * @param \Wyomind\Core\Helper\DataFactory $configHelperFactory
     * @param \Wyomind\Core\Helper\LicenseFactory $licenseHelperFactory
     * @param \Wyomind\Core\Model\ResourceModel\ConfigFactory $configFactory
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Wyomind\Core\Helper\DataFactory $configHelperFactory,
        \Wyomind\Core\Helper\LicenseFactory $licenseHelperFactory,
        \Wyomind\Core\Model\ResourceModel\ConfigFactory $configFactory
    )
    {
        $this->state = $state;
        $this->configHelperFactory = $configHelperFactory;
        $this->licenseHelperFactory = $licenseHelperFactory;
        $this->configFactory = $configFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('wyomind:license:request')
            ->setDescription(__('Request an additional license for the Wyomind modules'))
            ->setDefinition([
                new InputArgument(
                    "module", InputArgument::REQUIRED, __('The module for which you want to request the new license (eg: Wyomind_Core)')
                ),
                new InputArgument(
                    "activation-key", InputArgument::REQUIRED, __('The activation key to use to activate the license')
                )
            ]);
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $returnValue = \Magento\Framework\Console\Cli::RETURN_SUCCESS;

        try {
            $this->state->setAreaCode('adminhtml');
        } catch (\Exception $e) {

        }

        $coreHelper = $this->configHelperFactory->create();
        $licenseHelper = $this->licenseHelperFactory->create();
        $config = $this->configFactory->create();
        $coreVersion = $licenseHelper->getCoreVersion();
        $list = $licenseHelper->getModulesList();
        $module = $input->getArgument("module");
        $ak = $input->getArgument("activation-key");

        $found = false;
        foreach ($list as $info) {
            if ($module === $info["name"]) {
                $found = true;
                break;
            }
        }

        if (empty($ak)) {
            throw new \Exception(__("The activation key cannot be empty"));
        }
        if (!$found) {
            $message = __("The module %1 cannot be found", $module);
            $message .= "\n" . __("Available modules are:");
            foreach ($list as $info) {
                $message .= "\n  - " . $info['name'];
            }
            throw new \Exception($message);
        }

        $licensingMethod = 1;
        $licenseCode = "";

        $ext = strtolower($module);
        $prefix = $coreHelper->getPrefix($module);

        $currentVersion = $coreHelper->getStoreConfig($prefix . $ext . "/license/extension_version");

        $registeredVersion = $config->getDefaultValueByPath($prefix . $ext . "/license/extension_version");
        if ($registeredVersion == "") {
            $registeredVersion = $coreHelper->getStoreConfig($prefix . $ext . "/license/extension_version");
        }

        $domain = str_replace("{{unsecure_base_url}}", $config->getDefaultValueByPath("web/unsecure/base_url"), $config->getDefaultValueByPath("web/secure/base_url"));
        if ($domain == "") {
            $domain = str_replace("{{unsecure_base_url}}", $coreHelper->getStoreConfig("web/unsecure/base_url"), $coreHelper->getStoreConfig("web/secure/base_url"));
        }

        $soapParams = [
            "method" => "get",
            "rv" => ($registeredVersion != null) ? $registeredVersion : "",
            "cv" => ($currentVersion != null) ? $currentVersion : "",
            "namespace" => $ext,
            "activation_key" => $ak,
            "domain" => $domain,
            "magento" => $coreHelper->getMagentoVersion(),
            "licensemanager" => $coreVersion
        ];

        // licence deleted because wrong ak or ac
        if ($registeredVersion != "" && $registeredVersion != $currentVersion && $licenseCode) { // Extension upgrade
            $coreHelper->getStoreConfig($prefix . $ext . "/license/activation_code", "");
            $coreHelper->getStoreConfig($prefix . $ext . "/license/extension_version", $currentVersion);
            $output->writeln($licenseHelper->sprintfArray("upgrade", [$registeredVersion, $currentVersion]));
        } elseif ($ak && (!$licenseCode || empty($licenseCode)) && $licensingMethod) { // not yet activated --> automatic activation
            try {
                $options = ['location' => \Wyomind\Core\Helper\License::SOAP_URL, 'uri' => \Wyomind\Core\Helper\License::SOAP_URI];
                if (!class_exists("\SoapClient")) {
                    throw new RuntimeException();
                }
                $api = new \SoapClient(null, $options);
                $ws = $api->askNewLicense($soapParams);
                $wsResult = json_decode($ws);

                switch ($wsResult->status) {
                    case "success":

                    case "error":

                        $output->writeln($licenseHelper->sprintfArray("ws_success", [$wsResult->message]));
                        break;
                    default:
                        throw new \Exception(strip_tags($licenseHelper->sprintfArray("ws_error", [''])));


                }
            } catch (\RuntimeException $e) {

                throw new \Exception(__("SOAP request not allowed. Please enable SOAP."));
            }
        }

        return $returnValue;
    }
}