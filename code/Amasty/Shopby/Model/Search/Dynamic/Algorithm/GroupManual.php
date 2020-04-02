<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Search\Dynamic\Algorithm;

use Magento\Framework\Search\Adapter\OptionsInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Dynamic\Algorithm;
use Magento\Framework\Search\Dynamic\Algorithm\AlgorithmInterface;

/**
 * Class GroupManual
 * @package Amasty\Shopby\Model\Search\Dynamic\Algorithm
 */
class GroupManual implements AlgorithmInterface
{
    /**
     * @var Algorithm
     */
    private $algorithm;

    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * @var OptionsInterface
     */
    private $options;

    /**
     * @param DataProviderInterface $dataProvider
     * @param Algorithm $algorithm
     * @param OptionsInterface $options
     */
    public function __construct(
        DataProviderInterface $dataProvider,
        Algorithm $algorithm,
        OptionsInterface $options
    ) {
        $this->algorithm = $algorithm;
        $this->dataProvider = $dataProvider;
        $this->options = $options;
    }

    /**
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param EntityStorage $entityStorage
     * @return array
     */
    public function getItems(
        BucketInterface $bucket,
        array $dimensions,
        EntityStorage $entityStorage
    ) {
        $data = [];

        $aggregations = $this->dataProvider->getAggregations($entityStorage);
        $options = $this->options->get();
        if ($aggregations['count'] < $options['interval_division_limit']) {
            return [];
        }
        $this->algorithm->setStatistics(
            $aggregations['min'],
            $aggregations['max'],
            $aggregations['std'],
            $aggregations['count']
        );

        $this->algorithm->setLimits($aggregations['min'], $aggregations['max'] + 0.01);
        if (!count($dimensions['group'])) {
            throw new \RuntimeException('Not data for Decimal');
        }
        $interval = $this->dataProvider->getInterval($bucket, $dimensions, $entityStorage);
        foreach ($dimensions['group'] as $key => $range) {
            $count = count($interval->load(null, null, $range['min'], $range['max']));
            $dimensions['group'][$key]['count'] = $count;
        }
        $data = $this->prepareData($dimensions['group']);

        return $data;
    }

    /**
     * @param $ranges
     * @return array
     */
    public function prepareData($ranges)
    {
        $data = [];
        foreach ($ranges as $range) {
            $data[] = [
                'from' => $range['min'],
                'to' => $range['max'],
                'count' => $range['count'],
            ];
        }

        return $data;
    }
}
