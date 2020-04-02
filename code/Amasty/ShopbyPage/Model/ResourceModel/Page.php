<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;

class Page extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_amshopby_page', 'page_id');
    }

    /**
     * @return string
     */
    private function getStoreTable()
    {
        return $this->getTable('amasty_amshopby_page_store');
    }

    /**
     * @param int $pageId
     * @return array
     */
    public function lookupStoreIds($pageId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getStoreTable(),
            'store_id'
        )->where(
            'page_id = ?',
            $pageId
        );

        return $connection->fetchCol($select);
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function saveStores(AbstractModel $object)
    {
        list($oldStores, $newStores) = $this->resolveStoresInfo($object);
        $pageId = (int) $object->getId();
        $newStores = array_map(
            function ($store) {
                return (int)$store;
            },
            $newStores
        );

        if ($insert = array_diff($newStores, $oldStores)) {
            $this->insertByStores($pageId, $insert);
        }
        if ($delete = array_diff($oldStores, $newStores)) {
            $this->deleteByStores($pageId, $delete);
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     *
     * @return array
     */
    private function resolveStoresInfo($object)
    {
        $oldStores = $this->lookupStoreIds((int)$object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }

        return [$oldStores, $newStores];
    }

    /**
     * @param int $pageId
     * @param array $storeIds
     */
    private function deleteByStores($pageId, $storeIds)
    {
        $where = [
            'page_id = ?' => $pageId,
            'store_id IN (?)' => $storeIds
        ];

        $this->getConnection()->delete($this->getStoreTable(), $where);
    }

    /**
     * @param int $pageId
     * @param array $storeIds
     */
    private function insertByStores($pageId, $storeIds)
    {
        $data = [];

        foreach ($storeIds as $storeId) {
            $data[] = [
                'page_id' => $pageId,
                'store_id' => (int)$storeId
            ];
        }

        $this->getConnection()->insertMultiple($this->getStoreTable(), $data);
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('stores', $stores);
        }

        return parent::_afterLoad($object);
    }
}
