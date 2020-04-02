<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Layer\Filter\OnSale;

use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product as ConfigurableProduct;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        $this->storeManager = $storeManager;
        $this->resource = $resourceConnection;
        $this->customerSession = $customerSession;
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param null $storeId
     * @param bool $filterByCustomerGroup
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addOnSaleFilter(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        $storeId = null,
        $filterByCustomerGroup = true
    ) {
        $collection->addPriceData();
        $select = $collection->getSelect();
        if ($collection->getLimitationFilters()->isUsingPriceIndex()) {
            $select->where('price_index.final_price < price_index.price');
        }
    }

    /**
     * @param ConfigurableProduct\Collection $productCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function loadCatalogRule(ConfigurableProduct\Collection $productCollection)
    {
        if (!$productCollection->hasFlag('catalog_rule_loaded')) {
            $connection = $this->resource->getConnection();
            $store = $this->storeManager->getStore();
            $productCollection->getSelect()
                ->joinLeft(
                    ['catalog_rule' => $this->resource->getTableName('catalogrule_product_price')],
                    implode(' AND ', [
                        'catalog_rule.product_id = e.entity_id',
                        $connection->quoteInto('catalog_rule.website_id = ?', $store->getWebsiteId()),
                        $connection->quoteInto(
                            'catalog_rule.customer_group_id = ?',
                            $this->customerSession->getCustomerGroupId()
                        ),
                        $connection->quoteInto(
                            'catalog_rule.rule_date = ?',
                            $this->dateTime->formatDate($this->localeDate->scopeDate($store->getId()), false)
                        ),
                    ]),
                    [CatalogRulePrice::PRICE_CODE => 'rule_price']
                );
            $productCollection->setFlag('catalog_rule_loaded', true);
        }
    }
}
