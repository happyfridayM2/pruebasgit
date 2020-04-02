<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model\Indexer\Product\Eav\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Amasty\Shopby\Helper\Group as GroupHelper;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Catalog\Model\Indexer\Product\Eav\Action\Full as IndexerEavActionFull;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source as EavSource;
use Amasty\Shopby\Model\ResourceModel\GroupAttrOption\CollectionFactory as GroupOptionCollectionFactory;

/**
 * Class Adapter
 * @package Amasty\Shopby\Plugin\Catalog\Model\Indexer\Product\Eav\Action
 */
class Adapter
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
     * @var GroupHelper
     */
    private $helper;

    /**
     * @var \Amasty\Shopby\Model\ResourceModel\GroupAttrOption\Collection
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

    public function __construct(
        EavSource $eavSource,
        GroupHelper $helper,
        GroupOptionCollectionFactory $collectionFactory,
        MetadataPool $metadataPool,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->connection = $eavSource->getConnection();
        $this->indexTable = $eavSource->getMainTable();
        $this->helper = $helper;
        $this->groupOptionCollection = $collectionFactory->create();
        $this->entityMetadata = $metadataPool->getMetadata(ProductInterface::class);
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @throws \Exception
     */
    public function updateGroupedOptionsIndex(\Magento\Framework\DB\Select $select)
    {
        $productIndex = $this->connection->fetchAll($select);
        if (empty($productIndex)) {
            return;
        }

        $groupedIndexData = [];
        $groupedOptions = $this->getGroupedOptions();
        foreach ($productIndex as $key => $productIndexData) {
            $optionValue = $productIndexData['value'];
            if (isset($groupedOptions[$optionValue])) {
                foreach ($groupedOptions[$optionValue] as $groupedOptionId) {
                    $groupedIndexRow = $productIndexData;
                    $groupedIndexRow['value'] = $groupedOptionId;
                    $groupedIndexData[] = $groupedIndexRow;
                }
            }

            unset($productIndex[$key]); //reduce memory consumption
        }

        if (empty($groupedIndexData)) {
            return;
        }

        $this->connection->beginTransaction();

        // @codingStandardsIgnoreStart
        if (isset($groupedIndexData[0]['source_id'])) {
            $insertSql = 'INSERT IGNORE INTO %s (%s, attribute_id, store_id, `value`, source_id) VALUES  %s';
        } else {
            $insertSql = 'INSERT IGNORE INTO %s (%s, attribute_id, store_id, `value`) VALUES  %s';
        }
        // @codingStandardsIgnoreEnd

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
    public function getGroupedOptions()
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
}
