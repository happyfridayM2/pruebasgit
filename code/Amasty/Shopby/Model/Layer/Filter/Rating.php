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
use Magento\Framework\View\Element\BlockFactory;
use Amasty\Shopby\Model\Layer\Filter\Traits\CustomTrait;

/**
 * Class Rating
 * @package Amasty\Shopby\Model\Layer\Filter
 */
class Rating extends AbstractFilter
{
    use CustomTrait;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $attributeCode = 'rating_summary';

    /**
     * @var  BlockFactory
     */
    private $blockFactory;

    /**
     * @var \Amasty\Shopby\Model\Request
     */
    private $shopbyRequest;

    /**
     * @var array
     */
    private $stars = [
        1 => 20,
        2 => 40,
        3 => 60,
        4 => 80,
        5 => 100,
        //6 => -1
    ];

    /**
     * @var \Amasty\Shopby\Model\Search\Adapter\Mysql\AggregationAdapter
     */
    private $aggregationAdapter;

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
        BlockFactory $blockFactory,
        \Amasty\Shopby\Model\Search\Adapter\Mysql\AggregationAdapter $aggregationAdapter,
        \Amasty\Shopby\Model\Request $shopbyRequest,
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
        $this->_requestVar = 'rating';
        $this->scopeConfig = $scopeConfig;
        $this->blockFactory = $blockFactory;
        $this->aggregationAdapter = $aggregationAdapter;
        $this->shopbyRequest = $shopbyRequest;
        $this->searchEngine = $searchEngine;
        $this->settingHelper = $settingHelper;
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
        if (!isset($this->stars[$filter])) {
            return $this;
        }
        $this->setCurrentValue($filter);
        $condition = $this->stars[$filter];

        if ($filter == 6) {
            $condition = new \Zend_Db_Expr("IS NULL");
        }

        $this->getLayer()->getProductCollection()->addFieldToFilter('rating_summary', $condition);
        if ($filter < 5) {
            $name = __('%1 stars & up', $filter);
        } elseif ($filter == 1) {
            $name = __('%1 star & up', $filter);
        } else {
            $name = __('%1 stars', $filter);
        }

        $item = $this->_createItem($name, $filter);
        $this->getLayer()->getState()->addFilter($item);
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
            ->getValue('amshopby/rating_filter/label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $label;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        $position = (int) $this->scopeConfig
            ->getValue('amshopby/rating_filter/position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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

        $listData = [];

        $allCount = 0;
        for ($i = 5; $i >= 1; $i--) {
            $count = isset($optionsFacetedData[$i]) ? $optionsFacetedData[$i]['count'] : 0;

            $allCount += $count;

            $listData[] = [
                'label' => $this->getLabelHtml($i),
                'value' => $i,
                'count' => $allCount,
                'real_count' => $count,
            ];
        }

        foreach ($listData as $data) {
            if ($data['real_count'] < 1) {
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

    /**
     * @param int $countStars
     *
     * @return string
     */
    private function getLabelHtml($countStars)
    {
        if ($countStars == 6) {
            return __('Not Yet Rated');
        }
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->blockFactory->createBlock(\Magento\Framework\View\Element\Template::class);
        $block->setTemplate('Amasty_Shopby::layer/filter/item/rating.phtml');
        $block->setData('star', $countStars);
        $html = $block->toHtml();
        return $html;
    }
}
