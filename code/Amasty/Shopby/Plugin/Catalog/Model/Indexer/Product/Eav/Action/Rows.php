<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model\Indexer\Product\Eav\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Indexer\Product\Eav\Action\Rows as IndexerEavActionRows;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source as EavSource;

/**
 * Class Rows
 * @package Amasty\Shopby\Plugin\Catalog\Model\Indexer\Product\Eav\Action
 */
class Rows
{
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

    /**
     * @var array
     */
    private $productIds = [];

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
     * @param IndexerEavActionRows $indexer
     * @param null $ids
     * @return array
     */
    public function beforeExecute(IndexerEavActionRows $indexer, $ids)
    {
        $this->productIds = $ids;
        return [$ids];
    }

    /**
     * @param IndexerEavActionRows $indexer
     * @throws \Exception
     */
    public function afterExecute(IndexerEavActionRows $indexer)
    {
        if ($this->productIds) {
            $select = $this->connection
                ->select()
                ->distinct(true)
                ->from($this->indexTable)
                ->where('value IN(?)', array_keys($this->adapter->getGroupedOptions()));

            $select->where($this->entityMetadata->getIdentifierField() . ' IN(?)', $this->productIds);
            $this->adapter->updateGroupedOptionsIndex($select);
        }
    }
}
