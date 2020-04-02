<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Test\Unit\Model\Layer\Filter\Traits;

use Amasty\Shopby\Model\Layer\Filter\Category;
use Amasty\Shopby\Model\Layer\Filter\Traits\CustomTrait;
use Amasty\Shopby\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Search\Request;

/**
 * Class CustomTraitTest
 *
 * @see CustomTrait
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class CustomTraitTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var CustomTrait
     */
    private $model;

    /**
     * @var \Amasty\Shopby\Model\Request\Builder
     */
    private $builder;

    public function setUp()
    {
        $this->model = $this->getMockBuilder(CustomTrait::class)
            ->setMethods(['getMemRequestBuilder'])
            ->getMockForTrait();

        $this->builder = $this->getMockBuilder(\Amasty\Shopby\Model\Request\Builder::class)
            ->disableOriginalConstructor()
            ->setMethods(['removePlaceholder', 'create'])
            ->getMock();
        $this->builder->expects($this->any())->method('removePlaceholder')->will($this->returnValue(1));
        $this->builder->expects($this->any())->method('create')->willReturnCallback(
            function () {
                return $this->getObjectManager()->getObject(Request::class);
            }
        );
        $this->model->expects($this->any())->method('getMemRequestBuilder')->will($this->returnValue($this->builder));

    }

    /**
     * @covers CustomTrait::getAlteredQueryResponse
     *
     * @dataProvider getTestDatabase
     *
     * @throws \ReflectionException
     */
    public function testGetAlteredQueryResponse($value, $expectedResult = null)
    {
        $rating = $this->getObjectManager()->getObject(\Amasty\Shopby\Model\Layer\Filter\Rating::class);

        $this->setProperty($this->model, 'currentValue', $value);
        $this->setProperty($this->model, 'attributeCode', 1, $rating);

        $searchEngine = $this->createPartialMock(\Magento\Search\Model\SearchEngine::class, ['search']);
        $searchEngine->expects($this->any())->method('search')->will($this->returnValue($value));
        $this->setProperty($this->model, 'searchEngine', $searchEngine, Category::class);
        $resultOrigMethod = $this->invokeMethod($this->model, 'GetAlteredQueryResponse');
        $this->assertEquals($expectedResult, $resultOrigMethod);
    }

    /**
     * @return array
     */
    public function getTestDatabase()
    {
        return [
            [null],
            [1, 1],
        ];
    }
}
