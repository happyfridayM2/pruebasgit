<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Console\Command;

use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TODO REMOVE in future releases
 */
class OptimizeCommand extends ConsoleCommand
{
    const IMAGE_SETTING_ID = 'settings_id';

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Image\ForceOptimization
     */
    private $forceOptimization;

    /**
     * @var \Amasty\PageSpeedOptimizer\Api\QueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Image\GenerateQueue
     */
    private $generateQueue;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Model\Image\ForceOptimization $forceOptimization,
        \Amasty\PageSpeedOptimizer\Api\QueueRepositoryInterface $queueRepository,
        \Amasty\PageSpeedOptimizer\Model\Image\GenerateQueue $generateQueue,
        $name = null
    ) {
        parent::__construct($name);
        $this->forceOptimization = $forceOptimization;
        $this->queueRepository = $queueRepository;
        $this->generateQueue = $generateQueue;
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
            )
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
        $queueSize = $this->generateQueue->generateQueue($input->getOption(self::IMAGE_SETTING_ID));
        $counter = 0;

        /** @var \Symfony\Component\Console\Helper\ProgressBar $progressBar */
        $progressBar = ObjectManager::getInstance()->create(
            \Symfony\Component\Console\Helper\ProgressBar::class,
            [
                'output' => $output,
                'max' => ceil($queueSize/100)
            ]
        );
        $progressBar->setFormat(
            '<info>%message%</info> %current%/%max% [%bar%]'
        );
        $output->writeln('<info>Optimization Process Started.</info>');
        $progressBar->start();
        $progressBar->display();

        while (!$this->queueRepository->isQueueEmpty()) {
            $progressBar->setMessage('Process Images ' . (($counter++) * 100) . ' from ' . $queueSize . '...');
            $progressBar->display();
            $this->forceOptimization->execute(100);
            $progressBar->advance();
        }
        $progressBar->setMessage('Process Images ' . $queueSize . ' from ' . $queueSize . '...');
        $progressBar->display();
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('<info>Images were optimized successfully.</info>');
    }
}
