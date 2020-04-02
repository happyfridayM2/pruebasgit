<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Model\Config\Source\FeedStatus;
use Amasty\Feed\Model\Config\Source\StorageFolder;
use Amasty\Feed\Model\Export\Product;
use Amasty\Feed\Model\Filesystem\FeedOutput;
use Amasty\Feed\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class FeedExport
 *
 * Processing feed export
 */
class FeedExport
{
    /**
     * @var Export\ProductFactory
     */
    private $productExportFactory;

    /**
     * @var Export\Adapter\AdapterProvider
     */
    private $adapterProvider;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var FeedRepository
     */
    private $feedRepository;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var FeedOutput
     */
    private $feedOutput;

    /**
     * @var \Amasty\Feed\Model\Config
     */
    private $config;

    /**
     * @var bool
     */
    private $multiProcessMode;
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    public function __construct(
        \Amasty\Feed\Model\Export\ProductFactory $productExportFactory,
        \Amasty\Feed\Model\Export\Adapter\AdapterProvider $adapterProvider,
        FeedRepository $feedRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Amasty\Feed\Model\Filesystem\FeedOutput $feedOutput,
        Config $config,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        bool $multiProcessMode = false
    ) {
        $this->productExportFactory = $productExportFactory;
        $this->adapterProvider = $adapterProvider;
        $this->logger = $logger;
        $this->feedRepository = $feedRepository;
        $this->eventManager = $eventManager;
        $this->feedOutput = $feedOutput;
        $this->config = $config;
        $this->multiProcessMode = $multiProcessMode;
        $this->filesystem = $filesystem;
    }

    /**
     * @param FeedInterface $feed
     * @param string $filename
     * @param int $page
     *
     * @return \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter
     * @throws LocalizedException
     */
    public function getWriter(FeedInterface $feed, $filename, $page)
    {
        try {
            $destination = trim($this->config->getFilePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
            $writer = $this->adapterProvider->get(
                $feed->getFeedType(),
                [
                    'destination' => $destination,
                    'page' => $page
                ]
            )->initBasics($feed);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(__('Please correct the file format.'));
        }

        return $writer;
    }

    /**
     * @inheritdoc
     */
    public function export(FeedInterface $feed, $page, $productIds, $lastPage, $preview = false, $cronGenerated = false)
    {
        $fileName = $this->multiProcessMode
            ? $this->getChunkFileName($feed, $page)
            : $feed->getFilename();

        $result = $this->productExportFactory->create(['storeId' => $feed->getStoreId()])
            ->setPage($page)
            ->setWriter($this->getWriter($feed, $fileName, $this->multiProcessMode ? 0 : $page))
            ->setAttributes($this->getAttributes($feed))
            ->setParentAttributes($this->getAttributes($feed, true))
            ->setMatchingProductIds($productIds)
            ->setUtmParams($feed->getUtmParams())
            ->setStoreId($feed->getStoreId())
            ->setFormatPriceCurrency($feed->getFormatPriceCurrency())
            ->setCurrencyShow($feed->getFormatPriceCurrencyShow())
            ->setFormatPriceDecimals($feed->getFormatPriceDecimals())
            ->setFormatPriceDecimalPoint($feed->getFormatPriceDecimalPoint())
            ->setFormatPriceThousandsSeparator($feed->getFormatPriceThousandsSeparator())
            ->export($lastPage);

        if ($preview) {
            $this->feedOutput->delete($feed);

            return $result;
        }

        $feed->setGeneratedAt($cronGenerated ?: date('Y-m-d H:i:s'));
        $feed->setProductsAmount($feed->getProductsAmount() + count($productIds));

        $status = $lastPage && !$this->multiProcessMode
            ? FeedStatus::READY
            : FeedStatus::PROCESSING;
        $feed->setStatus($status);
        $this->feedRepository->save($feed);
        if ($feed->getStatus() == FeedStatus::READY) {
            $this->feedOutput->get($feed);
            $this->eventManager->dispatch('amfeed_export_end', ['feed' => $feed]);
        }

        return $result;
    }

    /**
     * @param FeedInterface $feed
     * @param bool          $parent
     *
     * @return array
     */
    public function getAttributes(FeedInterface $feed, $parent = false)
    {
        $attributes = [
            Product::PREFIX_BASIC_ATTRIBUTE => [],
            Product::PREFIX_PRODUCT_ATTRIBUTE => [],
            Product::PREFIX_INVENTORY_ATTRIBUTE => [],
            Product::PREFIX_PRICE_ATTRIBUTE => [],
            Product::PREFIX_CATEGORY_ATTRIBUTE => [],
            Product::PREFIX_CATEGORY_PATH_ATTRIBUTE => [],
            Product::PREFIX_MAPPED_CATEGORY_ATTRIBUTE => [],
            Product::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE => [],
            Product::PREFIX_CUSTOM_FIELD_ATTRIBUTE => [],
            Product::PREFIX_IMAGE_ATTRIBUTE => [],
            Product::PREFIX_GALLERY_ATTRIBUTE => [],
            Product::PREFIX_URL_ATTRIBUTE => [],
            Product::PREFIX_OTHER_ATTRIBUTES => [],
            Product::PREFIX_ADVANCED_ATTRIBUTE => []
        ];

        if ($feed->isCsv()) {
            $this->processingCsv($feed, $attributes, $parent);
        } elseif ($feed->isXml()) {
            $this->processingXml($feed, $attributes, $parent);
        }

        return $attributes;
    }

    /**
     * @param FeedInterface $feed
     * @param array         $attributes
     * @param bool          $parent
     */
    public function processingCsv(FeedInterface $feed, &$attributes, $parent)
    {
        foreach ($feed->getCsvField() as $field) {
            if (($parent && isset($field['parent']) && $field['parent'] == 'yes')
                || !$parent && isset($field['attribute'])
            ) {
                list($type, $code) = explode("|", $field['attribute']);

                if (array_key_exists($type, $attributes)) {
                    $attributes[$type][$code] = $code;
                }
            }
        }
    }

    /**
     * @param FeedInterface $feed
     * @param array         $attributes
     * @param bool          $parent
     */
    public function processingXml(FeedInterface $feed, &$attributes, $parent)
    {
        $regex = "#{(.*?)}#";

        preg_match_all($regex, $feed->getXmlContent(), $vars);

        if (isset($vars[1])) {
            foreach ($vars[1] as $attributeRow) {
                preg_match("/attribute=\"(.*?)\"/", $attributeRow, $attrReg);
                preg_match("/parent=\"(.*?)\"/", $attributeRow, $parentReg);

                if (isset($attrReg[1])) {
                    list($type, $code) = explode("|", $attrReg[1]);
                    $attributeParent = isset($parentReg[1]) ? $parentReg[1] : 'no';

                    if (($parent && ($attributeParent == 'yes' || $attributeParent == 'if_empty')) || !$parent) {
                        if (array_key_exists($type, $attributes)) {
                            $attributes[$type][$code] = $code;
                        }
                    }
                }
            }
        }
    }

    public function combineChunks(FeedInterface $feed)
    {
        if ($this->config->getStorageFolder() == StorageFolder::VAR_FOLDER) {
            $dir = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        } else {
            $dir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
        $targetDirectory = trim($this->config->getFilePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $firstFileName = $targetDirectory . $this->getChunkFileName($feed, 0);
        if (!$dir->isExist($firstFileName)) {
            return;
        }
        $content = $dir->readFile($firstFileName);
        $chunk = 1;
        while ($dir->isExist($fileName = $targetDirectory . $this->getChunkFileName($feed, $chunk++))) {
            $content .= $dir->readFile($fileName);
            $dir->delete($fileName);
        }
        $dir->writeFile($firstFileName, $content);
        $dir->renameFile($firstFileName, $targetDirectory. $feed->getFilename());
    }

    protected function getChunkFileName(FeedInterface $feed, int $page): string
    {
        return $feed->getFilename() . '_' . $page;
    }
}
