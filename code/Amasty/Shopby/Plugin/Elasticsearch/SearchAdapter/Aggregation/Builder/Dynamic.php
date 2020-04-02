<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Elasticsearch\SearchAdapter\Aggregation\Builder;

use Amasty\ElasticSearch\Model\Search\GetResponse\GetAggregations;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\Dynamic as DynamicBuilder;

/**
 * Class Dynamic
 * @package Amasty\Shopby\Plugin\Elasticsearch\SearchAdapter\Aggregation\Builder
 */
class Dynamic
{
    /**
     * @param GetAggregations $subject
     * @param \Closure $closure
     * @param RequestBucketInterface $bucket
     * @param array $dimensions
     * @param array $elasticResponse
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetDynamicBucket(
        GetAggregations $subject,
        \Closure $closure,
        RequestBucketInterface $bucket,
        array $dimensions,
        array $elasticResponse
    ) {
        $resultData = $closure($bucket, $dimensions, $elasticResponse);
        return $this->addSliderData($resultData, $elasticResponse['aggregations'][$bucket->getName()]);
    }

    /**
     * @param DynamicBuilder|\Mirasvit\SearchElastic\Adapter\Aggregation\DynamicBucket $builder
     * @param \Closure $closure
     * @param RequestBucketInterface $bucket
     * @param array $dimensions
     * @param array $queryResult
     * @param DataProviderInterface $dataProvider
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBuild(
        $builder,
        \Closure $closure,
        RequestBucketInterface $bucket,
        array $dimensions,
        array $queryResult,
        DataProviderInterface $dataProvider
    ) {
        $resultData = $closure($bucket, $dimensions, $queryResult, $dataProvider);
        return $this->addSliderData($resultData, $queryResult['aggregations'][$bucket->getName()]);
    }

    /**
     * Used in a price and decimal sliders.
     *
     * @param array $resultData
     * @param array $aggregation
     * @return array
     */
    private function addSliderData(array $resultData, array $aggregation)
    {
        if ($aggregation && isset($aggregation['min'])) {
            $resultData['data']['value'] = 'data';
            $resultData['data']['min'] = $aggregation['min'];
            $resultData['data']['max'] = $aggregation['max'];
            $resultData['data']['count'] = $aggregation['count'];
        }
       
        return $resultData;
    }
}
