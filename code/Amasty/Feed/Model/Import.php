<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

use Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Class Import
 */
class Import
{
    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    private $templates = [
        'google', 'bing', 'shopping'
    ];

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        SampleDataContext $sampleDataContext,
        CollectionFactory $collectionFactory,
        FeedRepository $feedRepository,
        Filesystem $filesystem,
        \Amasty\Base\Model\Serializer $serializer
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->collectionFactory = $collectionFactory;
        $this->serializer = $serializer;
        $this->filesystem = $filesystem;
        $this->feedRepository = $feedRepository;
    }

    public function install()
    {
        $dir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);

        foreach ($this->templates as $template) {
            $fileName = $dir->getRelativePath($this->fixtureManager->getFixture('Amasty_Feed::fixtures/' . $template));
            if (!$dir->isExist($fileName)) {
                continue;
            }

            try {
                $content = $dir->readFile($fileName);
            } catch (\Magento\Framework\Exception\FileSystemException $exception) {
                continue;
            }

            $data = $this->serializer->unserialize($content);

            if (is_array($data)) {
                $feedCollection = $this->collectionFactory->create()
                    ->addFieldToFilter('name', $data['name'])
                    ->addFieldToFilter('is_template', 1);

                if ($feedCollection->getSize() > 0) {
                    $items = $feedCollection->getItems();
                    end($items)->delete();
                }

                $feedModel = $this->feedRepository->getEmptyModel();
                $feedModel->setData($data);
                try {
                    $this->feedRepository->save($feedModel);
                } catch (LocalizedException $exception) {
                    continue;
                }
            }
        }
    }

    public function update($templates)
    {
        if (!is_array($templates)) {
            $templates = [$templates];
        }

        $this->templates = $templates;

        $this->install();
    }
}
