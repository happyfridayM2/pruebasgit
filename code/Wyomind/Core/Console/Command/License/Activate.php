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

class Activate extends Command
{
    /**
     * @var \Magento\Framework\Module\ModuleListFactory
     */
    protected $_state = null;
    /**
     * @var \Wyomind\Core\Helper\DataFactory|null
     */
    protected $_configHelperFactory = null;
    /**
     * @var \Wyomind\Core\Helper\LicenseFactory|null
     */
    public $_licenseHelperFactory = null;
    /**
     * @var \Wyomind\Core\Model\ResourceModel\ConfigFactory|null
     */
    protected $_configFactory = null;

    /**
     * @var \Magento\Framework\Module\Dir\ReaderFactory|null
     */
    protected $_directoryReaderFactory = null;
    /**
     * @var \Wyomind\Core\Helper\License
     */
    protected $_licenseHelper = null;
    /**
     * @var \Wyomind\Core\Helper\License
     */
    protected $_config = null;
    /**
     * @var \Wyomind\Core\Helper\Config
     */
    protected $_configHelper = null;

    /**
     * Activate constructor.
     * @param \Magento\Framework\App\State $state
     * @param \Wyomind\Core\Helper\DataFactory $dataHelperFactory
     * @param \Wyomind\Core\Helper\LicenseFactory $licenseHelperFactory
     * @param \Wyomind\Core\Model\ResourceModel\ConfigFactory $configFactory
     * @param \Magento\Framework\Module\Dir\ReaderFactory $directoryReaderFactory
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Wyomind\Core\Helper\DataFactory $dataHelperFactory,
        \Wyomind\Core\Helper\LicenseFactory $licenseHelperFactory,
        \Wyomind\Core\Model\ResourceModel\ConfigFactory $configFactory,
        \Magento\Framework\Module\Dir\ReaderFactory $directoryReaderFactory
    )
    {
        $this->_state = $state;
        $this->_configHelperFactory = $dataHelperFactory;
        $this->_licenseHelperFactory = $licenseHelperFactory;
        $this->_configFactory = $configFactory;
        $this->_directoryReaderFactory = $directoryReaderFactory;
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('wyomind:license:activate')
            ->setDescription(__('Activate the license for an Wyomind module'))
            ->setDefinition([
                new InputArgument(
                    "module", InputArgument::REQUIRED, __('The module for which you want to activate the license (eg: Wyomind_Core)')
                ),
                new InputArgument(
                    "activation-key", InputArgument::OPTIONAL, __('The activation key to use to activate the license')
                )
            ]);
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $returnValue = \Magento\Framework\Console\Cli::RETURN_SUCCESS;

        try {
            $this->_state->setAreaCode('adminhtml');
        } catch (\Exception $e) {

        }

        $this->_configHelper = $this->_configHelperFactory->create();
        $this->_licenseHelper = $this->_licenseHelperFactory->create();
        $this->_config = $this->_configFactory->create();

        $list = $this->_licenseHelper->getModulesList();
        $module = $input->getArgument("module");
        $ak = $input->getArgument("activation-key");

        if ($module === "all") {
            foreach ($list as $info) {

                $this->activate($info["name"], $output);

            }
        } else {


            $found = false;
            foreach ($list as $info) {
                if ($module === $info["name"]) {
                    $found = true;
                    break;
                }
            }


            if (!$found) {
                $message = __("The module %1 cannot be found", $module);
                $message .= "\n" . __("Available modules are:");
                foreach ($list as $info) {
                    $message .= "\n  - " . $info['name'];
                }
                throw new \Exception($message);
            }
            if (empty($ak)) {
                throw new \Exception(__("The activation key cannot be empty"));
            }

            $this->activate($module, $output, $ak);

        }
        return $returnValue;
    }

    /**
     * @param $module
     * @param $output
     * @param bool $ak
     * @throws \Exception
     */

    protected function activate($module, & $output, $ak = false)
    {


        $licenseCode = "";
        $coreVersion = $this->_licenseHelper->getCoreVersion();
        $ext = strtolower($module);

        $prefix = $this->_configHelper->getPrefix($module);
        $directory = $this->_directoryReaderFactory->create()->getModuleDir('', $module);
        $xml = simplexml_load_file($directory . "/etc/module.xml");

        $currentVersion = (string)$xml->module['setup_version'];


        if (!$ak) {
            $output->writeln(" ");

            $output->writeln("<fg=black;bg=yellow>" . __("Activating") . " " . $module . "</>");
            $ak = $this->_configHelper->getDefaultConfigUncrypted($prefix . str_ireplace("Wyomind_", "", $ext) . "/license/activation_key");
            if (empty($ak)) {
                $output->writeln("<error>" . __("Unable to activate: no license key found for") . " " . $module . "</error>");
                $output->writeln("<comment>" . __("Please run wyomind:license:activate") . " " . $module . " " . "<activation_key>" . "</comment>");

                return;
            }

        }


        $registeredVersion = $this->_config->getDefaultValueByPath($prefix . str_ireplace("Wyomind_", "", $ext) . "/license/extension_version");
        if ($registeredVersion == "") {
            $registeredVersion = $this->_configHelper->getStoreConfig($prefix . str_ireplace("Wyomind_", "", $ext) . "/license/extension_version");
        }

        $domain = str_replace("{{unsecure_base_url}}", $this->_config->getDefaultValueByPath("web/unsecure/base_url"), $this->_config->getDefaultValueByPath("web/secure/base_url"));
        if ($domain == "") {
            $domain = str_replace("{{unsecure_base_url}}", $this->_configHelper->getStoreConfig("web/unsecure/base_url"), $this->_configHelper->getStoreConfig("web/secure/base_url"));
        }

        $soapParams = [
            "method" => "get",
            "rv" => ($registeredVersion != null) ? $registeredVersion : "",
            "cv" => ($currentVersion != null) ? $currentVersion : "",
            "namespace" => $ext,
            "activation_key" => $ak,
            "domain" => $domain,
            "magento" => $this->_configHelper->getMagentoVersion(),
            "licensemanager" => $coreVersion
        ];


        // licence deleted because wrong ak or ac

        if ($registeredVersion != "" && $registeredVersion != $currentVersion && $licenseCode) { // Extension upgrade
            $this->_configHelper->setStoreConfig($prefix . str_ireplace("Wyomind_", "", $ext) . "/license/activation_code", "");
            $this->_configHelper->setStoreConfig($prefix . str_ireplace("Wyomind_", "", $ext) . "/license/extension_version", $currentVersion);
            $output->writeln($this->_licenseHelper->sprintfArray("upgrade", [$registeredVersion, $currentVersion]));
        } elseif ($ak && (!$licenseCode || empty($licenseCode))) { // not yet activated --> automatic activation
            try {
                $options = ['location' => \Wyomind\Core\Helper\License::SOAP_URL, 'uri' => \Wyomind\Core\Helper\License::SOAP_URI];
                if (!class_exists("\SoapClient")) {
                    throw new \RuntimeException();
                }
                $api = new \SoapClient(null, $options);

                $ws = $api->checkActivation($soapParams, true);
                $wsResult = json_decode($ws);

                switch ($wsResult->status) {
                    case "success":
                        $output->writeln($this->_licenseHelper->sprintfArray("ws_success", [$wsResult->message]));
                        $this->_configHelper->setStoreConfig($prefix . str_ireplace("Wyomind_", "", $ext) . "/license/extension_version", $wsResult->version);
                        $this->_configHelper->setStoreConfigCrypted($prefix . str_ireplace("Wyomind_", "", $ext) . "/license/activation_key", $ak);
                        $this->_configHelper->setStoreConfigCrypted($prefix . str_ireplace("Wyomind_", "", $ext) . "/license/activation_code", $wsResult->activation);

                        break;
                    case "error":
                        $this->_configHelper->setStoreConfig($prefix . str_ireplace("Wyomind_", "", $ext) . "/license/activation_code", "");

                        $output->writeln($this->_licenseHelper->sprintfArray("ws_success", [$wsResult->message]));
                        break;
                    default:
                        throw new \Exception(strip_tags($this->_licenseHelper->sprintfArray("ws_error", [''])));

                }
            } catch (\RuntimeException $e) {
                throw new \Exception(__("SOAP request not allowed. Please enable SOAP."));
            }
        }
    }
}