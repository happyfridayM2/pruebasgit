<?php

/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Core\Console\Command\License;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Status extends Command
{
    /**
     * @var \Magento\Framework\App\State|null
     */
    public $state = null;
    /**
     * @var \Wyomind\Core\Helper\LicenseFactory|null
     */
    public $licenseHelperFactory = null;
    /**
     * @var \Wyomind\Core\Helper\DataFactory|null
     */
    public $configHelperFactory = null;

    /**
     * Status constructor.
     * @param \Magento\Framework\App\State $state
     * @param \Wyomind\Core\Helper\LicenseFactory $licenseHelperFactory
     * @param \Wyomind\Core\Helper\DataFactory $configHelperFactory
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Wyomind\Core\Helper\LicenseFactory $licenseHelperFactory,
        \Wyomind\Core\Helper\DataFactory $configHelperFactory
    )
    {
        $this->state = $state;
        $this->licenseHelperFactory = $licenseHelperFactory;
        $this->configHelperFactory = $configHelperFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('wyomind:license:status')
            ->setDescription(__('Check the status of the licenses for the Wyomind modules'))
            ->setDefinition([]);
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $returnValue = \Magento\Framework\Console\Cli::RETURN_SUCCESS;

        try {
            $this->state->setAreaCode('adminhtml');
        } catch (\Exception $e) {

        }

        $licenseHelper = $this->licenseHelperFactory->create();
        $configHelper = $this->configHelperFactory->create();
        $list = $licenseHelper->getModulesList();

        $table = new \Symfony\Component\Console\Helper\Table($output);//$this->getHelperSet()->get('Table');
        $table->setHeaders(["Module", "Version", "Activation key", "Status"]);
        $table->setRows([]);

        foreach ($list as $info) {
            $prefix = $configHelper->getPrefix($info['name']);

            $activation_key = $configHelper->getStoreConfigUncrypted(strtolower($prefix . str_replace("Wyomind_","",$info['name'])) . "/license/activation_key");
            $license_code = $configHelper->getStoreConfigUncrypted(strtolower($prefix . str_replace("Wyomind_","",$info['name'])) . "/license/activation_code");

            if ($activation_key != "" && $license_code == "") {
                $status = "<error>invalidated</error>";
            } elseif ($license_code != '') {
                $status = "<fg=black;bg=green>success</>";
            } else {
                $status = "<fg=black;bg=yellow>pending</>";
            }

            $key = ($activation_key != '') ? $activation_key : "---";


            $data = [
                $info["name"],
                $info["setup_version"],
                $key,
                $status

            ];
            $table->addRow($data);
        }
        $output->writeln("");
        $table->render($output);
        $output->writeln("");

        return $returnValue;
    }
}