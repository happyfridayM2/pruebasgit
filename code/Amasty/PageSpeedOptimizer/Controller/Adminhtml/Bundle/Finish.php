<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Controller\Adminhtml\Bundle;

use Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\CollectionFactory;
use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Magento\Backend\App\Action;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Controller\ResultFactory;

class Finish extends Action
{
    /**
     * @var TypeListInterface
     */
    private $cache;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        TypeListInterface $cache,
        WriterInterface $configWriter,
        ConfigProvider $configProvider,
        CollectionFactory $collectionFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->cache = $cache;
        $this->configWriter = $configWriter;
        $this->configProvider = $configProvider;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->configWriter->save('amoptimizer/' . ConfigProvider::BUNDLE_HASH, null);
        $data = [];
        if (!$this->configProvider->isCloud()) {
            $this->configWriter->save('dev/js/merge_files', 1);
            $this->configWriter->save('dev/js/enable_js_bundling', 1);
            $this->configWriter->save('dev/js/minify_files', 1);
        } else {
            $result = [];
            /** @var \Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\Collection $collection */
            $collection = $this->collectionFactory->create();
            /** @var \Amasty\PageSpeedOptimizer\Api\Data\BundleFileInterface $item */
            foreach ($collection->getItems() as $item) {
                $result[$item->getArea()][$item->getTheme()][$item->getLocale()][] = $item->getFilename();
            }

            foreach ($result as $area => &$themeFiles) {
                foreach ($themeFiles as $theme => &$localeFiles) {
                    foreach ($localeFiles as $locale => &$files) {
                        $files = array_unique($files);
                    }
                }
            }

            $data = ['result' => '\'amoptimizer\' => [\'general\' => [\'enabled\' => 1],'
                . '\'javascript\' => [\'bundling_type\' => 1,\'is_cloud\' => 1,'
                . '\'bundling_files\' => \'' .
                json_encode($result) . '\']],'];
        }

        $this->cache->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($data);
    }
}
