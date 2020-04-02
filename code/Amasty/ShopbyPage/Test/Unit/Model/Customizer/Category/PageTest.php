<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Test\Unit\Model\Customizer\Category;

use Amasty\ShopbyPage\Api\Data\PageInterface;
use Amasty\ShopbyPage\Model\Customizer\Category\Page;
use Amasty\ShopbyPage\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PageTest
 *
 * @see Page
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class PageTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Page
     */
    private $model;

    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    private $shopbyHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $catalogConfig;

    /**
     * @var \Amasty\Shopby\Model\Request
     */
    private $amshopbyRequest;

    /**
     * @var \Amasty\ShopbyBase\Helper\FilterSetting
     */
    private $filterSettingHelper;

    public function setUp()
    {
        $this->shopbyHelper = $this->createMock(\Amasty\Shopby\Helper\Data::class);
        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->catalogConfig = $this->createMock(\Magento\Catalog\Model\Config::class);
        $this->amshopbyRequest = $this->createMock(\Amasty\Shopby\Model\Request::class);
        $this->filterSettingHelper = $this->createMock(\Amasty\ShopbyBase\Helper\FilterSetting::class);

        $this->model = $this->getObjectManager()->getObject(
            Page::class,
            [
                'shopbyHelper' => $this->shopbyHelper,
                'scopeConfig' => $this->scopeConfig,
                'catalogConfig' => $this->catalogConfig,
                'amshopbyRequest' => $this->amshopbyRequest,
                'filterSettingHelper' => $this->filterSettingHelper
            ]
        );
    }

    /**
     * @covers Page::matchCurrentFilters
     */
    public function testMatchCurrentFiltersWithFalseResult()
    {
        $this->shopbyHelper->expects($this->any())->method('getSelectedFiltersSettings')
            ->will($this->onConsecutiveCalls(['test']));
        $this->scopeConfig->expects($this->any())->method('isSetFlag')->will($this->onConsecutiveCalls(true));
        $pageData = $this->createMock(\Amasty\ShopbyPage\Api\Data\PageInterface::class);
        $pageData->expects($this->any())->method('getConditions')
            ->will($this->onConsecutiveCalls([], [['filter' => 1]], [['filter' => 1, 'value' => 1]]));
        $attribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $this->catalogConfig->expects($this->any())->method('getAttribute')->willReturn($attribute);
        $attribute->expects($this->any())->method('getId')->willReturn(1);
        $filterSetting = $this->createMock(\Amasty\ShopbyBase\Model\FilterSetting::class);
        $filterSetting->expects($this->any())->method('isMultiselect')->willReturn(false);
        $this->filterSettingHelper->expects($this->any())->method('getSettingByAttribute')->with($attribute)
            ->willReturn($filterSetting);

        $this->assertFalse($this->invokeMethod($this->model, 'matchCurrentFilters', [$pageData]));
        $this->assertFalse($this->invokeMethod($this->model, 'matchCurrentFilters', [$pageData]));
        $this->assertFalse($this->invokeMethod($this->model, 'matchCurrentFilters', [$pageData]));
    }

    /**
     * @covers Page::matchCurrentFilters
     */
    public function testMatchCurrentFiltersWithResultTrue()
    {
        $this->shopbyHelper->expects($this->any())->method('getSelectedFiltersSettings')
            ->will($this->onConsecutiveCalls([]));
        $this->scopeConfig->expects($this->any())->method('isSetFlag')->will($this->onConsecutiveCalls(false, true));
        $attribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $this->catalogConfig->expects($this->any())->method('getAttribute')->willReturn($attribute);
        $attribute->expects($this->any())->method('getId')->willReturn(false);

        $filterSetting = $this->createMock(\Amasty\ShopbyBase\Model\FilterSetting::class);
        $filterSetting->expects($this->any())->method('isMultiselect')->willReturn(false);
        $this->filterSettingHelper->expects($this->any())->method('getSettingByAttribute')->with($attribute)
            ->willReturn($filterSetting);

        $pageData = $this->createMock(\Amasty\ShopbyPage\Api\Data\PageInterface::class);
        $pageData->expects($this->any())->method('getConditions')
            ->will($this->onConsecutiveCalls([], [], ['test'], [['filter' => 1, 'value' => 1]]));

        $this->assertTrue($this->invokeMethod($this->model, 'matchCurrentFilters', [$pageData]));
        $this->assertTrue($this->invokeMethod($this->model, 'matchCurrentFilters', [$pageData]));
        $this->assertFalse($this->invokeMethod($this->model, 'matchCurrentFilters', [$pageData]));
        $this->assertTrue($this->invokeMethod($this->model, 'matchCurrentFilters', [$pageData]));
    }

    /**
     * @covers Page::isConditionNotMatched
     */
    public function testIsConditionNotMatchedWithFalse()
    {
        $attribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $this->catalogConfig->expects($this->any())->method('getAttribute')->willReturn($attribute);
        $this->amshopbyRequest->expects($this->any())->method('getParam')->willReturn('test');
        $attribute->expects($this->any())->method('getId')->willReturn(1);
        $filterSetting = $this->createMock(\Amasty\ShopbyBase\Model\FilterSetting::class);
        $filterSetting->expects($this->any())->method('isMultiselect')->willReturn(false);
        $this->filterSettingHelper->expects($this->any())->method('getSettingByAttribute')->with($attribute)
            ->willReturn($filterSetting);

        $this->assertFalse($this->invokeMethod($this->model, 'isConditionNotMatched', ['test', 'test']));
        $this->assertTrue($this->invokeMethod($this->model, 'isConditionNotMatched', ['test', 'code']));
    }

    /**
     * @covers Page::isConditionNotMatched
     */
    public function testIsConditionNotMatchedWithTrue()
    {
        $attribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $this->amshopbyRequest->expects($this->any())->method('getParam')->willReturn('code');
        $this->catalogConfig->expects($this->any())->method('getAttribute')->willReturn($attribute);
        $attribute->expects($this->any())->method('getId')->willReturn(1);
        $filterSetting = $this->createMock(\Amasty\ShopbyBase\Model\FilterSetting::class);
        $filterSetting->expects($this->any())->method('isMultiselect')->willReturn(true);
        $this->filterSettingHelper->expects($this->any())->method('getSettingByAttribute')->with($attribute)
            ->willReturn($filterSetting);

        $this->assertTrue($this->invokeMethod($this->model, 'isConditionNotMatched', ['test', 'test']));
        $this->assertFalse($this->invokeMethod($this->model, 'isConditionNotMatched', ['test', ['code']]));
    }

    /**
     * @covers Page::checkMultiselectAttribute
     */
    public function testCheckMultiselectAttribute()
    {
        $this->assertTrue(
            $this->invokeMethod($this->model, 'checkMultiselectAttribute', ['test', ['test', 'code'], false])
        );
        $this->assertFalse(
            $this->invokeMethod($this->model, 'checkMultiselectAttribute', ['test', 'test', false])
        );
    }

    /**
     * @covers Page::checkStrictMatch
     */
    public function testCheckStrictMatch()
    {
        $filter = $this->createMock(\Magento\Catalog\Model\Layer\Filter\AbstractFilter::class);
        $attribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $attribute->expects($this->any())->method('getAttributeId')->willReturn(1);
        $filter->expects($this->any())->method('hasData')->will($this->onConsecutiveCalls(false, true));
        $filter->expects($this->any())->method('getAttributeModel')->willReturn($attribute);
        $this->shopbyHelper->expects($this->any())->method('getSelectedFiltersSettings')
            ->will($this->onConsecutiveCalls([], ['test'], [['filter' => $filter]], [['filter' => $filter]]));
        $this->amshopbyRequest->expects($this->any())->method('getParam')->willReturn([]);

        $this->assertTrue($this->invokeMethod($this->model, 'checkStrictMatch', [[]]));
        $this->assertFalse($this->invokeMethod($this->model, 'checkStrictMatch', [[]]));
        $this->assertFalse($this->invokeMethod($this->model, 'checkStrictMatch', [['test']]));
        $this->assertFalse($this->invokeMethod($this->model, 'checkStrictMatch', [['filter' => 2]]));
    }

    /**
     * @covers Page::matchAppliedFilter
     */
    public function testMatchAppliedFilter()
    {
        $filter = $this->createMock(\Magento\Catalog\Model\Layer\Filter\AbstractFilter::class);
        $attribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $attribute->expects($this->any())->method('getAttributeId')->willReturn(1);
        $filterSetting = $this->createMock(\Amasty\ShopbyBase\Model\FilterSetting::class);
        $filterSetting->expects($this->any())->method('isMultiselect')
            ->will($this->onConsecutiveCalls(false, true));
        $this->filterSettingHelper->expects($this->any())->method('getSettingByAttribute')->with($attribute)
            ->willReturn($filterSetting);
        $filter->expects($this->any())->method('getAttributeModel')->willReturn($attribute);

        $this->assertTrue($this->invokeMethod($this->model, 'matchAppliedFilter', [$filter, []]));

        $this->assertFalse($this->invokeMethod($this->model, 'matchAppliedFilter', [$filter, [['filter' => 2]]]));

        $this->assertFalse($this->invokeMethod(
            $this->model,
            'matchAppliedFilter',
            [$filter, [['filter' => 1, 'value' => 1]]])
        );

        $this->assertFalse($this->invokeMethod(
            $this->model,
            'matchAppliedFilter',
            [$filter, [['filter' => 1, 'value' => 1]]])
        );
    }

    /**
     * @covers Page::getModifiedCategoryData
     */
    public function testGetModifiedCategoryData()
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->any())->method('getPosition')->will($this->onConsecutiveCalls('replace', 'after'));
        $this->assertEquals(1, $this->invokeMethod($this->model, 'getModifiedCategoryData', [$page, 1, 2, null]));
        $this->assertEquals(1, $this->invokeMethod($this->model, 'getModifiedCategoryData', [$page, 1, 2, '-']));
        $this->assertEquals('1-2', $this->invokeMethod($this->model, 'getModifiedCategoryData', [$page, 1, 2, '-']));
    }

    /**
     * @covers Page::insertIntoPosition
     */
    public function testInsertIntoPosition()
    {
        $this->assertEquals('1-2', $this->invokeMethod($this->model, 'insertIntoPosition', ['before', 1, 2, '-']));
        $this->assertEquals('2-1', $this->invokeMethod($this->model, 'insertIntoPosition', ['after', 1, 2, '-']));
        $this->assertEquals('1-5-6', $this->invokeMethod($this->model, 'insertIntoPosition', ['before', 1, '5-6', '-']));
        $this->assertEquals('5-6-1', $this->invokeMethod($this->model, 'insertIntoPosition', ['after', 1, '5-6', '-']));
    }
}
