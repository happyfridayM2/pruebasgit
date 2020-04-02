<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model\Indexer\Product\Eav\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Catalog\Model\Indexer\Product\Eav\Action\Full as IndexerEavActionFull;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source as EavSource;
use Amasty\Shopby\Plugin\Catalog\Model\Indexer\Product\Eav\Action\Adapter;

/**
 * Class Full
 * @package Amasty\Shopby\Plugin\Catalog\Model\Indexer\Product\Eav\Action
 */
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
     * @var Adapter
     */
    private $adapter;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadataInterface
     */
    private $entityMetadata;

    public function __construct(
        EavSource $eavSource,
        Adapter $adapter,
        MetadataPool $metadataPool
    ) {
        $this->connection = $eavSource->getConnection();
        $this->indexTable = $eavSource->getMainTable();
        $this->adapter = $adapter;
        $this->entityMetadata = $metadataPool->getMetadata(ProductInterface::class);
    }

    /**
     * @param IndexerEavActionFull $indexer
     */
    public function afterExecute(IndexerEavActionFull $indexer = null)
    {
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
                ->where('value IN(?)', array_keys($this->adapter->getGroupedOptions()));

            $betweenCondition = sprintf(
                '(%s BETWEEN %s AND %s)',
                $this->entityMetadata->getIdentifierField(),
                $this->connection->quote($batch['from']),
                $this->connection->quote($batch['to'])
            );

            $select->where($betweenCondition);

            $this->adapter->updateGroupedOptionsIndex($select);
        }
    }

    /**
     * @param AdapterInterface $adapter
     * @param $tableName
     * @param $linkField
     * @param $batchSize
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
