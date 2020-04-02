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
use Magento\Catalog\Model\Indexer\Product\Eav\Action\Row as IndexerEavActionRow;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source as EavSource;

/**
 * Class Row
 * @package Amasty\Shopby\Plugin\Catalog\Model\Indexer\Product\Eav\Action
 */
class Row
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
     * @var \Amasty\Shopby\Model\ResourceModel\GroupAttrOption\Collection
     */
    private $groupOptionCollection;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadataInterface
     */
    private $entityMetadata;

    /**
     * @var null
     */
    private $productId = null;

    /**
     * @var Adapter
     */
    private $adapter;

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
     * @param IndexerEavActionRow $indexer
     * @param null $id
     * @return array
     */
    public function beforeExecute(IndexerEavActionRow $indexer, $id = null)
    {
        $this->productId = $id;
        return [$id];
    }

    /**
     * @param IndexerEavActionRow $indexer
     *
     * @throws \Exception
     */
    public function afterExecute(IndexerEavActionRow $indexer)
    {
        if ($this->productId) {
            $select = $this->connection
                ->select()
                ->distinct(true)
                ->from($this->indexTable)
                ->where('value IN(?)', array_keys($this->adapter->getGroupedOptions()));

            $select->where($this->entityMetadata->getIdentifierField() . ' = ?', $this->productId);
            $this->adapter->updateGroupedOptionsIndex($select);
        }
    }
}
