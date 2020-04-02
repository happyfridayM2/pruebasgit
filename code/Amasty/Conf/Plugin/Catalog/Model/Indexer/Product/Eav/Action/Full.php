<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\Catalog\Model\Indexer\Product\Eav\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Amasty\Conf\Helper\Group as GroupHelper;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Catalog\Model\Indexer\Product\Eav\Action\Full as IndexerEavActionFull;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source as EavSource;
use Amasty\Conf\Model\ResourceModel\GroupAttrOption\CollectionFactory as GroupOptionCollectionFactory;
use Magento\Framework\Module\Manager as ModuleManager;

class Full
{
    const BATCH_SIZE = 3000;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $indexTable;

    /**
     * @var GroupHelper
     */
    private $helper;

    /**
     * @var \Amasty\Conf\Model\ResourceModel\GroupAttrOption\Collection
     */
    private $groupOptionCollection;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadataInterface
     */
    private $entityMetadata;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var array|null
     */
    private $groupedOptions = null;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        EavSource $eavSource,
        GroupHelper $helper,
        GroupOptionCollectionFactory $collectionFactory,
        MetadataPool $metadataPool,
        ModuleManager $moduleManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->connection = $eavSource->getConnection();
        $this->indexTable = $eavSource->getMainTable();
        $this->helper = $helper;
        $this->groupOptionCollection = $collectionFactory->create();
        $this->entityMetadata = $metadataPool->getMetadata(ProductInterface::class);
        $this->logger = $logger;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param IndexerEavActionFull|null $indexer
     */
    public function afterExecute(IndexerEavActionFull $indexer = null)
    {
        try {
            $this->indexGroupOptions();
        } catch (\Exception $exc) {
            $this->logger->error($exc->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function indexGroupOptions()
    {
        if (!$this->moduleManager->isEnabled('Amasty_Shopby')
            && $this->getGroupedOptions()
        ) {
            $batches = $this->getBatches(
                $this->connection,
                $this->indexTable,
                $this->entityMetadata->getIdentifierField(),
                self::BATCH_SIZE
            );

            foreach ($batches as $batch) {
                $select = $this->connection
                    ->select()
                    ->distinct(true)
                    ->from($this->indexTable)
                    ->where('value IN(?)', array_keys($this->getGroupedOptions()));

                $betweenCondition = sprintf(
                    '(%s BETWEEN %s AND %s)',
                    $this->entityMetadata->getIdentifierField(),
                    $this->connection->quote($batch['from']),
                    $this->connection->quote($batch['to'])
                );

                $select->where($betweenCondition);

                $this->addGroupedOptionsIndex($select);
            }
        }
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     *
     * @throws \Exception
     */
    private function addGroupedOptionsIndex(\Magento\Framework\DB\Select $select)
    {
        $productIndex = $this->connection->fetchAll($select);
        if (empty($productIndex)) {
            return;
        }

        $groupedIndexData = [];
        $groupedOptions = $this->getGroupedOptions();
        foreach ($productIndex as $key => $productIndexData) {
            $optionValue = $productIndexData['value'];

            foreach ($groupedOptions[$optionValue] as $groupedOptionId) {
                $groupedIndexRow = $productIndexData;
                $groupedIndexRow['value'] = $groupedOptionId;
                $groupedIndexData[] = $groupedIndexRow;
            }

            unset($productIndex[$key]); //reduce memory consumption
        }

        $this->connection->beginTransaction();

        if (isset($groupedIndexData[0]['source_id'])) {
            // phpcs:ignore
            $insertSql = 'INSERT IGNORE INTO %s (%s, attribute_id, store_id, `value`, source_id) VALUES  %s';
        } else {
            // phpcs:ignore
            $insertSql = 'INSERT IGNORE INTO %s (%s, attribute_id, store_id, `value`) VALUES  %s';
        }

        $query = sprintf(
            $insertSql,
            $this->indexTable,
            $this->entityMetadata->getIdentifierField(),
            $this->prepareInsertValues($groupedIndexData)
        );

        $this->connection->query($query);
        $this->connection->commit();
    }
    /**
     * @param array $insertionData
     *
     * @return string
     */
    private function prepareInsertValues(array &$insertionData)
    {
        $statement = '';

        foreach ($insertionData as $key => $insertion) {
            $statement .= sprintf('(%s),', implode(',', $insertion));
            unset($insertionData[$key]); //reduce memory consumption
        }

        return rtrim($statement, ',');
    }

    /**
     * @return array
     */
    private function getGroupedOptions()
    {
        if ($this->groupedOptions === null) {
            $groupAttributesWithOptions = $this->helper->getGroupsWithOptions();
            $this->groupedOptions = [];

            foreach ($groupAttributesWithOptions as $groupId => $value) {
                foreach ($value['options'] as $option) {
                    $this->groupedOptions[$option][] = GroupHelper::LAST_POSSIBLE_OPTION_ID - $groupId;
                }
            }
        }

        return $this->groupedOptions;
    }

    /**
     * @param AdapterInterface $adapter
     * @param $tableName
     * @param $linkField
     * @param $batchSize
     *
     * @return \Generator
     */
    private function getBatches(AdapterInterface $adapter, $tableName, $linkField, $batchSize)
    {
        $maxLinkFieldValue = $adapter->fetchOne(
            $adapter->select()->from(
                ['entity' => $tableName],
                [
                    'max_value' => new \Zend_Db_Expr('MAX(entity.' . $linkField . ')')
                ]
            )
        );

        /** @var int $truncatedBatchSize size of the last batch that is smaller than expected batch size */
        $truncatedBatchSize = $maxLinkFieldValue % $batchSize;
        /** @var int $fullBatchCount count of the batches that have expected batch size */
        $fullBatchCount = ($maxLinkFieldValue - $truncatedBatchSize) / $batchSize;

        for ($batchIndex = 0; $batchIndex < $fullBatchCount; $batchIndex ++) {
            yield ['from' => $batchIndex * $batchSize + 1, 'to' => ($batchIndex + 1) * $batchSize];
        }
        // return the last batch if it has smaller size
        if ($truncatedBatchSize > 0) {
            yield ['from' => $fullBatchCount * $batchSize + 1, 'to' => $maxLinkFieldValue];
        }
    }
}
