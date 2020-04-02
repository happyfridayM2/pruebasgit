<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\BucketBuilder;

use Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\BucketBuilderInterface as BucketBuilderInterface;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;

/**
 * Class RatingSummary
 * @package Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\BucketBuilder
 */
class RatingSummary implements BucketBuilderInterface
{
    /**
     * @param RequestBucketInterface $bucket
     * @param array $queryResult
     * @return array
     */
    public function build(
        RequestBucketInterface $bucket,
        array $queryResult
    ) {
        $values = [];
        if (isset($queryResult['aggregations'][$bucket->getName()]['buckets'])) {
            foreach ($queryResult['aggregations'][$bucket->getName()]['buckets'] as $resultBucket) {
                $key = (int)floor($resultBucket['key'] / 20);
                $previousCount = isset($values[$key]['count']) ? $values[$key]['count'] : 0;
                $values[$key] = [
                    'value' => $key,
                    'count' => $resultBucket['doc_count'] + $previousCount,
                ];
            }
        }

        return $values;
    }
}
