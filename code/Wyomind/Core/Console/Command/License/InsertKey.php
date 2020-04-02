<?php

namespace Wyomind\Core\Console\Command\License;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\RuntimeException;

class InsertKey extends Command
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
     * Insert Code constructor.
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
        $this->setName('wyomind:license:insertkey')
            ->setDescription(__('Insert the activation key for a Wyomind module'))
            ->setDefinition([
                new InputArgument(
                    "module", InputArgument::REQUIRED, __('The module for which you want to add the activation key (eg: Wyomind_Core)')
                ),
                new InputArgument(
                    "activation-key", InputArgument::REQUIRED, __('The activation key to insert to activate the license')
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
        $code = $input->getArgument("activation-key");


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
        if (empty($code)) {
            throw new \Exception(__("The activation key cannot be empty"));
        }

        $this->insert($module, $output, $code);


        return $returnValue;
    }

    /**
     * @param $module
     * @param $output
     * @param bool $code
     * @throws \Exception
     */

    protected function insert($module, & $output, $code = false)
    {


        $ext = strtolower($module);
        $prefix = $this->_configHelper->getPrefix($module);

        try {
            $this->_configHelper->setStoreConfigCrypted($prefix . str_ireplace("Wyomind_", "", $ext) . "/license/activation_key", $code);
            $output->writeln("<bg=green;fg=black>" . __("Activation key inserted for") . " " . $module . "</>");
            return;
        } catch (\RuntimeException $e) {

            throw new \Exception(__("Unable to insert the activation key."));
        }

    }
}