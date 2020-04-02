<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Model\ResourceModel\Page;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;

/**
 * Class Collection
 *
 * @package Amasty\ShopbyPage\Model\ResourceModel\Page
 */
class Collection extends AbstractCollection
{
    const PAGE_STORE_TABLE = 'amasty_amshopby_page_store';

    /**
     * @var string
     */
    protected $_idFieldName = 'page_id';

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\ShopbyPage\Model\Page::class, \Amasty\ShopbyPage\Model\ResourceModel\Page::class);
        $this->_map['fields']['page_id'] = 'main_table.page_id';
        $this->_map['fields']['store_id'] = 'store_table.store_id';
    }

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     *
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = Store::DEFAULT_STORE_ID;
        }

        $this->addFilter('store_id', ['in' => $store], 'public');

        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string|null $linkField
     *
     * @return void
     */
    protected function joinStoreRelationTable($tableName, $linkField)
    {
        if ($this->getFilter('store_id')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable($tableName)],
                'main_table.' . $linkField . ' = store_table.' . $linkField,
                []
            )->group(
                'main_table.' . $linkField
            );
        }

        parent::_renderFiltersBefore();
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('amasty_amshopby_page_store', 'page_id');
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return $this|Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @return AbstractCollection
     */
    protected function _afterLoad()
    {
        $linkField = 'page_id';

        $linkedIds = $this->getColumnValues($linkField);
        if (count($linkedIds)) {
            $storesData = $this->getStoreData($linkField, $linkedIds);
            if ($storesData) {
                foreach ($this->getItems() as $item) {
                    $linkedId = $item->getData($linkField);
                    if (!isset($storesData[$linkedId])) {
                        continue;
                    }

                    list($storeId, $storeCode) = $this->getStoreDataForItem($storesData[$linkedId]);
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_code', $storeCode);
                    $item->setData('store_id', $storesData[$linkedId]);
                }
            }
        }

        return parent::_afterLoad();
    }

    /**
     * @param string $linkField
     * @param array $linkedIds
     *
     * @return array
     */
    protected function getStoreData($linkField, $linkedIds)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['amasty_amshopby_page_store' => $this->getTable(self::PAGE_STORE_TABLE)])
            ->where('amasty_amshopby_page_store.' . $linkField . ' IN (?)', $linkedIds);
        $result = $connection->fetchAll($select);

        $storesData = [];
        foreach ($result as $storeData) {
            $storesData[$storeData[$linkField]][] = $storeData['store_id'];
        }

        return $storesData;
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreDataForItem($data)
    {
        $storeIdKey = array_search(Store::DEFAULT_STORE_ID, $data, true);
        if ($storeIdKey !== false) {
            $stores = $this->_storeManager->getStores(false, true);
            $storeId = current($stores)->getId();
            $storeCode = key($stores);
        } else {
            $storeId = current($data);
            $storeCode = $this->_storeManager->getStore($storeId)->getCode();
        }

        return [$storeId, $storeCode];
    }
}
