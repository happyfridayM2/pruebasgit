<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Layer\Filter;

use Magento\Framework\Exception\StateException;
use Magento\Search\Model\SearchEngine;
use Amasty\Shopby\Model\Layer\Filter\IsNew\Helper as IsNewHelper;
use Amasty\Shopby\Model\Layer\Filter\Traits\CustomTrait;

/**
 * Class IsNew
 * @package Amasty\Shopby\Model\Layer\Filter
 */
class IsNew extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
{
    use CustomTrait;

    const FILTER_NEW = 1;
    const FILTER_NOT_NEW = 0;

    /**
     * @var string
     */
    private $attributeCode = 'am_is_new';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Shopby\Model\Search\Adapter\Mysql\AggregationAdapter
     */
    private $aggregationAdapter;

    /**
     * @var \Amasty\Shopby\Model\Request
     */
    private $shopbyRequest;

    /**
     * @var IsNewHelper
     */
    private $isNewHelper;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

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
        IsNewHelper $isNewHelper,
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
        $this->settingHelper = $settingHelper;
        $this->_requestVar = 'am_is_new';
        $this->scopeConfig = $scopeConfig;
        $this->aggregationAdapter = $aggregationAdapter;
        $this->shopbyRequest = $shopbyRequest;
        $this->isNewHelper = $isNewHelper;
        $this->searchEngine = $searchEngine;
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

        if (!in_array($filter, [self::FILTER_NEW])) {
            return $this;
        }

        $this->setCurrentValue($filter);

        if ($filter == self::FILTER_NEW) {
            $name = __('Yes');
            $this->getLayer()->getProductCollection()->addFieldToFilter('am_is_new', $filter);
            /**
             * @TODO remove this construction usage after 2.5.1
             * $this->isNewHelper->addNewFilter($this->getLayer()->getProductCollection());
             */
            $this->getLayer()->getState()->addFilter($this->_createItem($name, $filter));
        }

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
            ->getValue('amshopby/am_is_new_filter/label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $label;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        $position = (int) $this->scopeConfig
            ->getValue('amshopby/am_is_new_filter/position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $position;
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

        $isNew = isset($optionsFacetedData[1]) ? $optionsFacetedData[1]['count'] : 0;

        $listData = [
            [
                'label' => __('New'),
                'value' => self::FILTER_NEW,
                'count' => $isNew,
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
