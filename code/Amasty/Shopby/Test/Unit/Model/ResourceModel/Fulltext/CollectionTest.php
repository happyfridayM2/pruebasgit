<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


use Amasty\Shopby\Model\ResourceModel\Fulltext\Collection;
use Amasty\Shopby\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class Collection
 *
 * @see Collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const STORE_ID = '0';

    const TEST_FIELD = 'test';

    const TEST_FAIL_FIELD = 'test_fail';

    /**
     * @var Collection|MockObject
     */
    private $collection;

    public function setUp()
    {
        $this->collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();

        $requestBuilder = $this->getObjectManager()->getObject(\Amasty\Shopby\Model\Request\Builder::class);

        $scopeConfig = $this->createMock(\Magento\Framework\App\Config::class);
        $scopeConfig->expects($this->any())->method('getValue')
            ->with(\Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory::XML_PATH_RANGE_CALCULATION)
            ->willReturn('test');

        $this->setProperty($this->collection, 'requestBuilder', $requestBuilder, Collection::class);
        $this->setProperty($this->collection, '_scopeConfig', $scopeConfig, Collection::class);
    }

    /**
     * @covers Collection::getMemRequestBuilder
     */
    public function testGetMemRequestBuilder()
    {
        $this->collection->expects($this->any())->method('getStoreId')->willReturn(self::STORE_ID);

        $result = $this->collection->getMemRequestBuilder();
        $this->assertInstanceOf(\Amasty\Shopby\Model\Request\Builder::class, $result);

        $result = $this->collection->getMemRequestBuilder();
        $this->assertInstanceOf(\Amasty\Shopby\Model\Request\Builder::class, $result);
    }

    /**
     * @covers Collection::getFacetedData
     *
     * @expectedException \Magento\Framework\Exception\StateException
     */
    public function testGetFacetedData()
    {
        $agregationValue = $this->createMock(\Magento\Framework\Search\Response\Aggregation\Value::class);
        $agregationValue->expects($this->any())->method('getMetrics')
            ->willReturn(
                ['value' => 'test_val']
            );

        $bucket = $this->getObjectManager()->getObject(
            \Magento\Framework\Search\Response\Bucket::class,
            ['values' => [$agregationValue]]
        );

        $aggregation = $this->getObjectManager()
            ->getObject(
                \Magento\Framework\Search\Response\Aggregation::class,
                ['buckets' => ['test_bucket' => $bucket]]
            );

        $queryResponse = $this->createMock(\Magento\Framework\Search\Response\QueryResponse::class);
        $queryResponse->expects($this->any())->method('getAggregations')->willReturn($aggregation);

        $result = $this->collection->getFacetedData(self::TEST_FIELD, $queryResponse);

        $this->assertEquals(['test_val' => ['value' => 'test_val']], $result);

        $this->collection->getFacetedData(self::TEST_FAIL_FIELD, $queryResponse);
    }
}
