<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


use Amasty\Shopby\Model\Layer\FilterList;
use Amasty\Shopby\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class FilterList
 *
 * @see FilterList
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class FilterListTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var FilterList|MockObject
     */
    private $filterList;

    /**
     * @var \Magento\Framework\Registry|MockObject $registry
     */
    private $registry;

    /**
     * @var \Magento\Catalog\Model\Layer|MockObject $layer
     */
    private $layer;

    public function setUp()
    {
        $this->registry = $this->getObjectManager()->getObject(\Magento\Framework\Registry::class);
        $this->layer = $this->createMock(\Magento\Catalog\Model\Layer::class);

        $config = $this->createMock(\Amasty\Shopby\Helper\Config::class);
        $config->expects($this->any())->method('isCategoryFilterEnabled')->willReturn(false);

        $this->filterList = $this->getMockBuilder(FilterList::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateAllFilters', 'getFilterBlockPosition'])
            ->getMock();

        $this->setProperty($this->filterList, 'registry', $this->registry, FilterList::class);
        $this->setProperty($this->filterList, 'config', $config, FilterList::class);
    }

    /**
     * @covers FilterList::getAllFilters
     */
    public function testGetAllFilters()
    {
        $allFilters = $this->getFiltersForTest();
        $this->filterList->expects($this->any())->method('generateAllFilters')->with($this->layer)
            ->will($this->returnValue($allFilters));

        $result = $this->filterList->getAllFilters($this->layer);
        $this->assertInstanceOf(
            \Magento\Catalog\Model\Layer\Filter\AbstractFilter::class,
            $result['filter2']
        );

        $result = $this->filterList->getAllFilters($this->layer);
        $this->assertInstanceOf(
            \Magento\Catalog\Model\Layer\Filter\AbstractFilter::class,
            $result['filter2']
        );
    }

    /**
     * @covers FilterList::filterByPlace
     *
     * @dataProvider placesDataProvider
     */
    public function testFilterByPlace($filterName, $place)
    {
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $category->expects($this->at(0))->method('getData')
            ->with('page_layout')
            ->willReturn('1column');
        $category->expects($this->at(1))->method('getData')
            ->with('page_layout')
            ->willReturn('2column');
        $this->layer->expects($this->any())->method('getCurrentCategory')->willReturn($category);

        $this->filterList->expects($this->any())->method('getFilterBlockPosition')->willReturn(1);
        $this->setProperty($this->filterList, 'currentPlace', $place, FilterList::class);

        $result = $this->invokeMethod(
            $this->filterList,
            'filterByPlace',
            [$this->getFiltersForTest(), $this->layer]
        );

        $this->assertArrayHasKey(
            $filterName, $result
        );
    }

    /**
     * Return array of filters for test
     *
     * @return array
     */
    public function getFiltersForTest()
    {
        $categoryFilter = $this->createMock(\Amasty\Shopby\Model\Layer\Filter\Category::class);
        $ratingFilter = $this->createMock(\Amasty\Shopby\Model\Layer\Filter\Rating::class);

        return [
            'filter1' => $categoryFilter,
            'filter2' => $ratingFilter
        ];
    }

    /**
     * DataProvider for testFilterByPlace
     *
     * @return array
     */
    public function placesDataProvider()
    {
        return [
            ['filter1', 'sidebar'],
            ['filter2', 'top']
        ];
    }
}
