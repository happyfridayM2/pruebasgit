<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model\ResourceModel\OptionSetting;

use Magento\Framework\DB\Select;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;

/**
 * OptionSetting Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var Option\CollectionFactory
     */
    private $optionCollectionFactory;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Option\CollectionFactory $optionCollectionFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    /**
     * Collection protected constructor
     */
    protected function _construct()
    {
        $this->_init(
            \Amasty\ShopbyBase\Model\OptionSetting::class,
            \Amasty\ShopbyBase\Model\ResourceModel\OptionSetting::class
        );
        $this->_idFieldName = $this->getResource()->getIdFieldName();
    }

    /**
     * @param string $filterCode
     * @param int $optionId
     * @param int $storeId
     * @return $this
     */
    public function addLoadParams($filterCode, $optionId, $storeId)
    {
        $listStores = [0];
        if ($storeId > 0) {
            $listStores[] = $storeId;
        }

        $this->addFieldToFilter('filter_code', $filterCode)
            ->addFieldToFilter('value', $optionId)
            ->addFieldToFilter('store_id', $listStores)
            ->addOrder('store_id', self::SORT_ORDER_DESC);
        return $this;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getHardcodedAliases($storeId)
    {
        $select = $this->getSelect();
        $select->reset(Select::COLUMNS);
        $select->columns('filter_code');
        $select->columns('value');
        if ($storeId === 0) {
            $select->columns('url_alias');
            $select->where('`url_alias` <> ""');
            $select->where('`store_id` = ' . $storeId);
        } else {
            $urlAlias = 'IFNULL(`current_table`.`url_alias`, `main_table`.`url_alias`)';
            $select->joinLeft(
                ['current_table' => $this->getMainTable()],
                '`current_table`.`value` = `main_table`.`value`'
                . " AND `current_table`.`store_id` = $storeId"
                . ' AND `current_table`.`url_alias` <> ""',
                ['url_alias' => $urlAlias]
            );
            $select->where('`main_table`.`store_id` = ?', 0);
            $select->where("$urlAlias  <> ?", '');
        }

        $data = $select->getConnection()->fetchAll($select);
        return $data;
    }

    /**
     * @param $value
     * @param $storeId
     * @return mixed
     */
    public function getValueFromMagentoEav($value, $storeId)
    {
        $optionCollection = $this->optionCollectionFactory->create()
            ->addFieldToFilter('main_table.option_id', $value)
            ->addFieldToFilter('option.store_id', [0, $storeId])
            ->setOrder('option.store_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);
        $optionCollection->getSelect()
            ->setPart('columns', [])
            ->join(
                ['option' => $optionCollection->getTable('eav_attribute_option_value')],
                'option.option_id = main_table.option_id',
                ['value']
            );

        return $this->getConnection()->fetchOne($optionCollection->getSelect());
    }

    /**
     * @return $this
     */
    public function addTitleToCollection()
    {
        $this->getSelect()->joinInner(
            ['amshopbybrand_option' => $this->getTable('eav_attribute_option')],
            'main_table.value = amshopbybrand_option.option_id',
            []
        );
        $this->join(
            ['option' => $this->getTable('eav_attribute_option_value')],
            'option.option_id = main_table.value'
        );
        $this->getSelect()->columns('IF(main_table.title = "", option.value, main_table.title) as title');
        $this->getSelect()->columns(
            'IF(main_table.meta_title = "", option.value, main_table.meta_title) as meta_title'
        );
        $this->getSelect()->group('option_setting_id');

        return $this;
    }
}
