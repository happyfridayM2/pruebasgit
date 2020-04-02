<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model\ResourceModel\Unsubscribe;

/**
 * Class AlertProvider
 */
class AlertProvider
{
    const STOCK_TYPE = 'stock';

    const PRICE_TYPE = 'price';

    const REMOVE_ALL = 'all';

    /**
     * @var array
     */
    private $alertFactory = [];

    public function __construct(
        \Magento\ProductAlert\Model\PriceFactory $priceFactory,
        \Magento\ProductAlert\Model\StockFactory $stockFactory
    ) {
        $this->alertFactory[self::PRICE_TYPE] = $priceFactory;
        $this->alertFactory[self::STOCK_TYPE] = $stockFactory;
    }

    /**
     * @param string $type
     * @param int $productId
     * @param array $subscribeConditions
     *
     * @return null
     */
    public function getAlertModel($type, $productId, $subscribeConditions)
    {
        $collection = null;

        if (isset($this->alertFactory[strtolower($type)])) {
            $collection = $this->alertFactory[strtolower($type)]->create()->getCollection();
        }
        if (empty($collection)) {
            return null;
        }

        if (strcmp($productId, self::REMOVE_ALL) != 0) {
            $collection->addFieldToFilter('parent_id', $productId);
        }
        foreach ($subscribeConditions as $field => $value) {
            $collection->addFieldToFilter($field, $value);
        }

        return $collection;
    }
}
