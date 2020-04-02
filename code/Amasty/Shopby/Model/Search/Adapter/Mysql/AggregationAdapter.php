<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

namespace Amasty\Shopby\Model\Search\Adapter\Mysql;

use Amasty\Shopby\Model\Adapter\Mysql\Aggregation\GroupDataProviderFactory;
use Magento\Framework\Search\RequestInterface;

/**
 * Class AggregationAdapter
 * @package Amasty\Shopby\Model\Search\Adapter\Mysql
 */
class AggregationAdapter
{
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Mapper
     */
    private $mapper;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory
     */
    private $temporaryStorageFactory;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Container
     */
    private $aggregationContainer;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderContainer
     */
    private $dataProviderContainer;

    public function __construct(
        \Magento\Framework\Search\Adapter\Mysql\Mapper $mapper,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory,
        \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Container $aggregationContainer,
        \Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderContainer $dataProviderContainer
    ) {
        $this->mapper = $mapper;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->aggregationContainer = $aggregationContainer;
        $this->dataProviderContainer = $dataProviderContainer;
    }

    /**
     * @param RequestInterface $request
     * @param $attributeCode
     * @return array
     */
    public function getBucketByRequest(RequestInterface $request, $attributeCode)
    {
        $query = $this->mapper->buildQuery($request);
        $temporaryStorage = $this->temporaryStorageFactory->create();
        $documentsTable = $temporaryStorage->storeDocumentsFromSelect($query);
        $dataProvider = $this->dataProviderContainer->get($request->getIndex());

        $bucketAggregation = $request->getAggregation();
        $attributeCode = $attributeCode . "_bucket";

        $currentBucket = null;
        foreach ($bucketAggregation as $requestBucket) {
            if ($requestBucket->getName() == $attributeCode) {
                $currentBucket = $requestBucket;
                break;
            }
        }

        if ($currentBucket === null) {
            return [];
        }

        $aggregationBuilder = $this->aggregationContainer->get($currentBucket->getType());

        $responseBucket = $aggregationBuilder->build(
            $dataProvider,
            $request->getDimensions(),
            $currentBucket,
            $documentsTable
        );

        return $responseBucket;
    }

    /**
     * @param $data
     * @return int
     */
    protected function calcProducts($data)
    {
        $array = [];
        $count = 0;
        foreach ($data as $value) {
            if (!in_array($value['entity_id'], $array)) {
                $array[] = $value['entity_id'];
                $count++;
            }
        }

        return $count;
    }
}
