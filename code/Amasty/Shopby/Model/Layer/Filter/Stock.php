<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Layer\Filter;

use Magento\Framework\Exception\StateException;
use Magento\Search\Model\SearchEngine;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Amasty\Shopby\Model\Layer\Filter\Traits\CustomTrait;
use \Magento\Store\Model\ScopeInterface;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface as StockConfigurationInterface;

/**
 * Layer category filter
 */
class Stock extends AbstractFilter
{
    use CustomTrait;

    const FILTER_IN_STOCK = 1;

    const FILTER_OUT_OF_STOCK = 2;

    private $attributeCode = 'stock_status';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Amasty\Shopby\Model\Search\Adapter\Mysql\AggregationAdapter
     */
    private $aggregationAdapter;

    /**
     * @var \Amasty\Shopby\Model\Request
     */
    private $shopbyRequest;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var int
     */
    private $filterOutStock = 0;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var \Amasty\Shopby\Helper\FilterSetting
     */
    private $settingHelper;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Shopby\Model\Search\Adapter\Mysql\AggregationAdapter $aggregationAdapter,
        \Amasty\Shopby\Model\Request $shopbyRequest,
        StockConfigurationInterface $stockConfiguration,
        SearchEngine $searchEngine,
        \Amasty\Shopby\Helper\FilterSetting $settingHelper,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->_requestVar = 'stock';
        $this->scopeConfig = $scopeConfig;
        $this->aggregationAdapter = $aggregationAdapter;
        $this->shopbyRequest = $shopbyRequest;
        $this->stockConfiguration = $stockConfiguration;
        $this->searchEngine = $searchEngine;
        $this->settingHelper = $settingHelper;
        $searchEngineType = $scopeConfig->getValue(EngineInterface::CONFIG_ENGINE_PATH, ScopeInterface::SCOPE_STORE);
        if (strpos($searchEngineType, 'elasticsearch') !== false) {
            $this->filterOutStock = self::FILTER_OUT_OF_STOCK;
        }
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        if ($this->isApplied()) {
            return $this;
        }

        $filter = $this->shopbyRequest->getFilterParam($this);
        if (!in_array($filter, [self::FILTER_IN_STOCK, self::FILTER_OUT_OF_STOCK])) {
            return $this;
        }

        $this->setCurrentValue($filter);
        $isFilterOutOfStock = $filter == self::FILTER_OUT_OF_STOCK;
        if ($this->isStockSourceQty()) {
            $qty = (float) $this->stockConfiguration->getMinQty($this->getStoreId());
            $qtyCondition = "IF({{table}}.use_config_min_qty, $qty, {{table}}.min_qty)";
            $condition = "{{table}}.stock_id = 1 AND (e.type_id != 'simple' OR {{table}}.qty > $qtyCondition)";
            if ($isFilterOutOfStock) {
                $condition = "{{table}}.stock_id = 1 AND e.type_id = 'simple' AND {{table}}.qty <= $qtyCondition";
            }

            $this->getLayer()
                ->getProductCollection()
                ->joinField(
                    'qty',
                    'cataloginventory_stock_item',
                    'qty',
                    'product_id = entity_id',
                    $condition,
                    'inner'
                );
        } else {
            $applyFilter = $isFilterOutOfStock ? $this->filterOutStock : self::FILTER_IN_STOCK;
            $this->getLayer()->getProductCollection()->addFieldToFilter($this->attributeCode, $applyFilter);
        }

        $name = $filter == self::FILTER_IN_STOCK ? __('In Stock') : __('Out of Stock');
        $this->getLayer()->getState()->addFilter($this->_createItem($name, $filter));
        return $this;
    }

    /**
     * Get filter name
     *
     * @return \Magento\Framework\Phrase
     */
    public function getName()
    {
        $label = $this->scopeConfig
            ->getValue('amshopby/stock_filter/label', ScopeInterface::SCOPE_STORE);
        return $label;
    }

    public function getPosition()
    {
        $position = (int) $this->scopeConfig
            ->getValue('amshopby/stock_filter/position', ScopeInterface::SCOPE_STORE);
        return $position;
    }

    /**
     * @return bool
     */
    private function isStockSourceQty()
    {
         $stockSource = $this->scopeConfig
            ->getValue('amshopby/stock_filter/stock_source', ScopeInterface::SCOPE_STORE);
        return $stockSource === \Amasty\Shopby\Model\Source\StockFilterSource::QTY;
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        if ($this->isHide()) {
            return [];
        }

        try {
            $optionsFacetedData = $this->getFacetedData();
        } catch (StateException $e) {
            $optionsFacetedData = [];
        }

        $inStock = isset($optionsFacetedData[self::FILTER_IN_STOCK])
            ? $optionsFacetedData[self::FILTER_IN_STOCK]['count'] : 0;
        $outStock = isset($optionsFacetedData[$this->filterOutStock])
            ? $optionsFacetedData[$this->filterOutStock]['count'] : 0;

        $listData = [
            [
                'label' => __('In Stock'),
                'value' => self:: FILTER_IN_STOCK,
                'count' => $inStock,
            ],
            [
                'label' => __('Out of Stock'),
                'value' => self:: FILTER_OUT_OF_STOCK,
                'count' => $outStock,
            ]
        ];

        foreach ($listData as $data) {
            if ($data['count'] < 1) {
                continue;
            }
            $this->itemDataBuilder->addItemData(
                $data['label'],
                $data['value'],
                $data['count']
            );
        }

        return $this->itemDataBuilder->build();
    }
}
