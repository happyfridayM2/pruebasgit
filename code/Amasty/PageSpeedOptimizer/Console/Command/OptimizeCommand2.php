<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Console\Command;

use Amasty\PageSpeedOptimizer\Console\Command\Operation\Optimize;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OptimizeCommand2 extends ConsoleCommand
{
    const IMAGE_SETTING_ID = 'settings_id';
    const JOBS_AMOUNT = 'jobs';

    /**
     * @var Optimize
     */
    private $optimizeCommand;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Console\Command\Operation\Optimize $optimizeCommand,
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider,
        $name = null
    ) {
        $this->optimizeCommand = $optimizeCommand;
        $this->configProvider = $configProvider;

        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::IMAGE_SETTING_ID,
                '-i',
                InputOption::VALUE_OPTIONAL,
                'Image Settings Id'
            ),
            new InputOption(
                self::JOBS_AMOUNT,
                '-j',
                InputOption::VALUE_OPTIONAL,
                'Enable parallel processing using the specified number of jobs.',
                $this->configProvider->getMaxJobsCount()
            ),
        ];

        $this->setName('amasty:optimizer:optimize')
            ->setDescription('Run image optimization script.')
            ->setDefinition($options);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->optimizeCommand->execute($input, $output);
    }
}
