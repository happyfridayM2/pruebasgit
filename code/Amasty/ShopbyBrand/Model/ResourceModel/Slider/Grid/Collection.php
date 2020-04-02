<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Model\ResourceModel\Slider\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\Collection as BrandCollection;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Magento\Store\Model\Store;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;

/**
 * Class Collection
 * Collection for displaying grid of slider brands
 */
class Collection extends BrandCollection implements SearchResultInterface
{
    /** @var AggregationInterface */
    protected $_aggregations;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Option\CollectionFactory
     */
    private $optionCollectionFactory;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Option\CollectionFactory $optionCollectionFactory,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $optionCollectionFactory,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_scopeConfig = $scopeConfig;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
        $this->_prepareCollection();
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    /**
     * add current attribute and default store_id filters
     * @return $this
     */
    protected function _prepareCollection()
    {
        $attrCode   = $this->_scopeConfig->getValue(
            'amshopby_brand/general/attribute_code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $filterCode = \Amasty\ShopbyBase\Helper\FilterSetting::ATTR_PREFIX . $attrCode;
        $this->addFieldToFilter(OptionSettingInterface::FILTER_CODE, $filterCode);
        $this->addFieldToFilter('main_table.' . OptionSettingInterface::STORE_ID, Store::DEFAULT_STORE_ID);
        $this->getSelect()->joinInner(
            ['amshopbybrand_option' => $this->getTable('eav_attribute_option')],
            'main_table.value = amshopbybrand_option.option_id',
            []
        );
        $this->join(
            ['option' => $this->getTable('eav_attribute_option_value')],
            'option.option_id = main_table.value'
        );
        $this->getSelect()->columns(
            'IF(main_table.title != \'\', main_table.title, option.value) as title'
        );
        $this->getSelect()->columns(
            'IF(main_table.meta_title != \'\', main_table.meta_title, option.value) as meta_title'
        );
        $this->getSelect()->group('option_setting_id');

        return $this;
    }

    /**
     * add second order by title
     *
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        $titleField = \Amasty\ShopbyBase\Api\Data\OptionSettingInterface::TITLE;
        if ($field != $titleField) {
            parent::setOrder($field, $direction);
            $field = $titleField;
            $direction = 'ASC';
        }

        return parent::setOrder($field, $direction);
    }

    /**
     * Remove default store_id == 0 filer.
     * @return $this
     * @throws \Zend_Db_Select_Exception
     */
    protected function _removeStoreFilter()
    {
        $conditions = $this->getSelect()->getPart('where');
        foreach ($conditions as $index => $cond) {
            if (strpos($cond, OptionSettingInterface::STORE_ID) !== false) {
                unset($conditions[$index]);
            }
        }
        $this->getSelect()->setPart('where', $conditions);
        return $this;
    }

    /**
     * Remove brands with store_id = 0 from selection, which have values in the current store view.
     * @param int $requestedStoreId
     * @return $this
     */
    protected function _removeDefault($requestedStoreId)
    {
        $storeField = OptionSettingInterface::STORE_ID;
        $query = $this->getConnection()->select()->from($this->getMainTable(), [
            'value',
            "MAX(IF(`$storeField` = $requestedStoreId, $requestedStoreId, " . Store::DEFAULT_STORE_ID . '))'
        ])->group('value');
        $this->getSelect()->where('(main_table.value, main_table.' . $storeField . ') IN (' . $query . ')');

        return $this;
    }

    /**
     * Correctly process store_id filter
     * @param array|string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == OptionSettingInterface::STORE_ID && is_array($condition)) {
            $requestedStoreId = (int)array_pop($condition);
            $this->_removeStoreFilter();
            $this->_removeDefault($requestedStoreId);
            $condition = [$requestedStoreId,  Store::DEFAULT_STORE_ID];
            $field = 'main_table.' . $field;
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->_aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->_aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
