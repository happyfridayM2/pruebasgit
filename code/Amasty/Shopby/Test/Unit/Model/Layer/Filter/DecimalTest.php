<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Test\Unit\Model\Layer\Filter;

use Amasty\Shopby\Model\Layer\Filter\Decimal;
use Amasty\Shopby\Test\Unit\Traits;
use Amasty\ShopbyBase\Model\FilterSetting;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class DecimalTest
 *
 * @see Decimal
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DecimalTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Decimal
     */
    private $model;

    /**
     * @var MockObject|\Amasty\Shopby\Helper\FilterSetting
     */
    private $settingHelper;

    /**
     * @var MockObject|\Amasty\Shopby\Helper\Group
     */
    private $groupHelper;

    /**
     * @var MockObject|\Amasty\Shopby\Model\ResourceModel\Fulltext\Collection
     */
    private $productCollection;

    public function setUp()
    {
        $this->settingHelper = $this->createMock(\Amasty\Shopby\Helper\FilterSetting::class);
        $filterItemFactory = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\ItemFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $filterItem = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\Item::class)
            ->setMethods(['setFilter', 'setLabel', 'setValue', 'setCount'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeModel = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $this->groupHelper = $this->createMock(\Amasty\Shopby\Helper\Group::class);
        $searchEngine = $this->createMock(\Magento\Search\Model\SearchEngine::class);
        $layer = $this->createMock(\Magento\Catalog\Model\Layer::class);
        $this->productCollection = $this->createMock(\Amasty\Shopby\Model\ResourceModel\Fulltext\Collection::class);
        $requestBuilder = $this->createMock(\Amasty\Shopby\Model\Request\Builder::class);
        $request = $this->createMock(\Magento\Framework\Search\RequestInterface::class);
        $priceCurrency = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->setMethods(['hasMessages', 'addErrorMessage'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $filterItemFactory->expects($this->any())->method('create')->willReturn($filterItem);
        $filterItem->expects($this->any())->method('setFilter')->willReturn($filterItem);
        $filterItem->expects($this->any())->method('setLabel')->willReturn($filterItem);
        $filterItem->expects($this->any())->method('setValue')->willReturn($filterItem);
        $filterItem->expects($this->any())->method('setValue')->willReturn($filterItem);
        $searchEngine->expects($this->any())->method('search')->willReturn(true);
        $layer->expects($this->any())->method('getProductCollection')->willReturn($this->productCollection);
        $this->productCollection->expects($this->any())->method('getMemRequestBuilder')->willReturn($requestBuilder);
        $requestBuilder->expects($this->any())->method('removePlaceholder')->willReturn($requestBuilder);
        $requestBuilder->expects($this->any())->method('setAggregationsOnly')->willReturn($requestBuilder);
        $requestBuilder->expects($this->any())->method('create')->willReturn($request);
        $messageManager->expects($this->any())->method('hasMessages')->willReturn(true);
        $messageManager->expects($this->any())->method('addErrorMessage')->willReturn(true);
        $priceCurrency->expects($this->any())->method('format')->willReturnArgument(0);

        $this->model = $this->getObjectManager()->getObject(
            Decimal::class,
            [
                'settingHelper' => $this->settingHelper,
                'filterItemFactory' => $filterItemFactory,
                'groupHelper' => $this->groupHelper,
                'searchEngine' => $searchEngine,
                'messageManager' => $messageManager,
                'priceCurrency' => $priceCurrency,
            ]
        );

        $this->model->setAttributeModel($attributeModel);
        $this->setProperty($this->model, '_catalogLayer', $layer);
    }

    /**
     * @covers Decimal::getItemsCountIfNotIgnoreRanges
     */
    public function testGetItemsCountIfNotIgnoreRanges()
    {
        $settingFilter = $this->getObjectManager()->getObject(FilterSetting::class);
        $this->settingHelper->expects($this->any())->method('getSettingByLayerFilter')->willReturn($settingFilter);
        $this->setProperty($this->model, 'facetedData', ['10_20' => ['count' => 2]]);

        $this->assertEquals(1, $this->model->getItemsCount());
    }

    /**
     * @covers Decimal::getItemsCountIfIgnoreRanges
     */
    public function testGetItemsCountIfIgnoreRanges()
    {
        $data = [
            'data' =>['count' => 1, 'min' => 1, 'max' => 2],
            '10_20' => ['count' => 2]
        ];
        $settingFilter = $this->getObjectManager()->getObject(FilterSetting::class);
        $settingFilter->setDisplayMode(3);
        $this->settingHelper->expects($this->any())->method('getSettingByLayerFilter')->willReturn($settingFilter);
        $this->setProperty($this->model, 'facetedData', ['data' =>['count' => 1, 'min' => 0, 'max' => 0]]);
        $this->assertEquals(0, $this->model->getItemsCount());

        $this->setProperty($this->model, 'facetedData', $data);

        $this->assertEquals(1, $this->model->getItemsCount());
    }

    /**
     * @covers Decimal::isIgnoreRanges
     */
    public function testIsIgnoreRanges()
    {
        $settingFilter = $this->getObjectManager()->getObject(FilterSetting::class);
        $settingFilter->setDisplayMode(3);
        $this->settingHelper->expects($this->any())->method('getSettingByLayerFilter')->willReturn($settingFilter);

        $this->assertTrue($this->invokeMethod($this->model, 'isIgnoreRanges'));

        $settingFilter->setDisplayMode(2);
        $this->assertTrue($this->invokeMethod($this->model, 'isIgnoreRanges'));

        $settingFilter->setDisplayMode(1);
        $this->assertFalse($this->invokeMethod($this->model, 'isIgnoreRanges'));
    }

    /**
     * @covers Decimal::getItemsData
     */
    public function testGetItemsData()
    {
        $data = [
            'data' =>['count' => 1, 'min' => 1, 'max' => 2],
            '10_20' => ['count' => 2]
        ];
        $this->groupHelper->expects($this->any())->method('getGroupAttributeMinMaxRanges')->willReturn(['10-20' => 'range']);
        $this->setProperty($this->model, 'facetedData', ['data' => 1]);
        $this->assertEquals([], $this->invokeMethod($this->model, '_getItemsData'));

        $this->setProperty($this->model, 'facetedData', $data);
        $result = $this->invokeMethod($this->model, '_getItemsData');
        $this->assertEquals('10-20', $result[0]['value']);
        $this->assertEquals('2', $result[0]['count']);
        $this->assertEquals('10', $result[0]['from']);
        $this->assertEquals('20', $result[0]['to']);
    }

    /**
     * @covers Decimal::getAlteredQueryResponse
     */
    public function testGetAlteredQueryResponse()
    {
        $this->assertNull($this->invokeMethod($this->model, 'getAlteredQueryResponse'));
        $this->setProperty($this->model, 'currentValue', 'test');
        $this->assertTrue($this->invokeMethod($this->model, 'getAlteredQueryResponse'));
    }

    /**
     * @covers Decimal::getFacetedData
     */
    public function testGetFacetedData()
    {
        $this->productCollection->expects($this->any())->method('getFacetedData')->willReturn(['test1', 'test2']);
        $this->assertEquals(['test1', 'test2'], $this->invokeMethod($this->model, 'getFacetedData'));

        $this->setProperty($this->model, 'facetedData', ['test']);
        $this->assertEquals(['test'], $this->invokeMethod($this->model, 'getFacetedData'));
    }

    /**
     * @covers Decimal::getFacetedDataException
     */
    public function testGetFacetedDataException()
    {
        $this->productCollection->expects($this->any())->method('getFacetedData')
            ->willThrowException(new \Magento\Framework\Exception\StateException(__('exceprion')));
        $this->assertEquals([], $this->invokeMethod($this->model, 'getFacetedData'));
    }

    /**
     * @covers Decimal::getGroupRanges
     */
    public function testGetGroupRanges()
    {
        $this->groupHelper->expects($this->any())->method('getGroupAttributeMinMaxRanges')->willReturn(['10-20' => 'range']);
        $this->assertEquals(
            'range',
            (string)$this->invokeMethod($this->model, 'getGroupRanges', ['10', '20'])
        );
    }

    /**
     * @covers Decimal::getGroupRangesWithoutRange
     */
    public function testGetGroupRangesWithoutRange()
    {
        $this->groupHelper->expects($this->any())->method('getGroupAttributeMinMaxRanges')->willReturn([]);
        $this->assertEquals(
            '',
            (string)$this->invokeMethod($this->model, 'getGroupRanges', ['10', '20'])
        );
    }

    /**
     * @covers Decimal::getDefaultRangeLabel
     */
    public function testGetDefaultRangeLabel()
    {
        $settingFilter = $this->createMock(FilterSetting::class);
        $settingFilter->expects($this->any())->method('getUnitsLabelUseCurrencySymbol')->willReturn(true);

        $this->assertEquals(
            '10 - 19.99',
            (string)$this->invokeMethod($this->model, 'getDefaultRangeLabel', ['10', '20', $settingFilter])
        );

        $this->assertEquals(
            ' - 19.99',
            (string)$this->invokeMethod($this->model, 'getDefaultRangeLabel', ['', '20', $settingFilter])
        );
    }

    /**
     * @covers Decimal::getDefaultRangeLabelWithoutData
     */
    public function testGetDefaultRangeLabelWithoutData()
    {
        $settingFilter = $this->createMock(FilterSetting::class);
        $settingFilter->expects($this->any())->method('getUnitsLabelUseCurrencySymbol')->willReturn(false);

        $this->assertEquals(
            '',
            (string)$this->invokeMethod($this->model, 'getDefaultRangeLabel', ['10', '20', $settingFilter])
        );
    }

    /**
     * @covers Decimal::getRangeForState
     */
    public function testGetRangeForState()
    {
        $settingFilter = $this->getObjectManager()->getObject(FilterSetting::class);
        $settingFilter->setPositionLabel(0)->setData('units_label', '$');

        $this->assertEquals(
            '$10 - $19.99',
            (string)$this->invokeMethod($this->model, 'getRangeLabel', ['10', '20', $settingFilter])
        );

        $settingFilter->setPositionLabel(1)->setData('units_label', '$');
        $this->assertEquals(
            '10$ - 19.99$',
            (string)$this->invokeMethod($this->model, 'getRangeLabel', ['10', '20', $settingFilter])
        );

        $this->assertEquals(
            '10$ and above',
            (string)$this->invokeMethod($this->model, 'getRangeLabel', ['10', '', $settingFilter])
        );

        $settingFilter->setDisplayMode(\Amasty\Shopby\Model\Source\DisplayMode::MODE_SLIDER);
        $this->assertEquals(
            '10$ - 20$',
            (string)$this->invokeMethod($this->model, 'getRangeLabel', ['10', '20', $settingFilter])
        );

        $settingFilter->setDisplayMode(\Amasty\Shopby\Model\Source\DisplayMode::MODE_FROM_TO_ONLY);
        $this->assertEquals(
            '10$ - 20$',
            (string)$this->invokeMethod($this->model, 'getRangeLabel', ['10', '20', $settingFilter])
        );
    }

    /**
     * @covers Decimal::formatLabelForStateAndRange
     */
    public function testFormatLabelForStateAndRange()
    {
        $settingFilter = $this->getObjectManager()->getObject(FilterSetting::class);
        $settingFilter->setPositionLabel(0)->setData('units_label', '$');
        $this->assertEquals(
            '$10',
            $this->invokeMethod($this->model, 'formatLabelForStateAndRange', ['10', $settingFilter])
        );
        $settingFilter->setPositionLabel(1)->setData('units_label', '$');
        $this->assertEquals(
            '10$',
            $this->invokeMethod($this->model, 'formatLabelForStateAndRange', ['10', $settingFilter])
        );
    }
}
