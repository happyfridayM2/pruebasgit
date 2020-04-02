<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Ui\DataProvider\Form\Subscription;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Amasty\Xnotif\Model\ResourceModel\Stock\Subscription\Collection;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ProductCollectionFactory $productCollectionFactory,
        Collection $collection,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->collection = $collection;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data = parent::getData();
        if ($data['totalRecords'] > 0 && isset($data['items'][0]['alert_stock_id'])) {
            $subscriptionId = (int)$data['items'][0]['alert_stock_id'];
            $data[$subscriptionId] = $data['items'][0];
        }

        return $data;
    }
}
