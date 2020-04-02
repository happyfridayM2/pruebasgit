<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Test\Unit\Model\Layer\Filter;

use Amasty\Shopby\Model\Layer\Filter\Price;
use Amasty\Shopby\Test\Unit\Traits;
use Amasty\ShopbyBase\Model\FilterSetting;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PriceTest
 *
 * @see Price
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Price
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
     * @var MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var MockObject|\Amasty\Shopby\Model\Request
     */
    private $shopbyRequest;

    /**
     * @var MockObject|\Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var MockObject|\Magento\Framework\Registry
     */
    private $storeMock;

    /**
     * @var MockObject|\Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private $dataProvider;

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
        $requestBuilder = $this->createMock(\Amasty\Shopby\Model\Request\Builder::class);
        $request = $this->createMock(\Magento\Framework\Search\RequestInterface::class);
        $priceCurrency = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $this->shopbyRequest = $this->createMock(\Amasty\Shopby\Model\Request::class);
        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->coreRegistry = $this->createMock(\Magento\Framework\Registry::class);
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->dataProvider = $this->createMock(\Magento\Catalog\Model\Layer\Filter\DataProvider\Price::class);
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
                 ->setMethods(['getId', 'getCurrentCurrencyRate'])
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
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
        $requestBuilder->expects($this->any())->method('removePlaceholder')->willReturn($requestBuilder);
        $requestBuilder->expects($this->any())->method('setAggregationsOnly')->willReturn($requestBuilder);
        $requestBuilder->expects($this->any())->method('create')->willReturn($request);
        $messageManager->expects($this->any())->method('hasMessages')->willReturn(true);
        $messageManager->expects($this->any())->method('addErrorMessage')->willReturn(true);
        $this->storeMock->expects($this->any())->method('getId')->willReturn(1);
        $storeManager->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->dataProvider->expects($this->any())->method('getAdditionalRequestData')->willReturn(10);
        $priceCurrency->expects($this->any())->method('format')->willReturnArgument(0);

        $this->model = $this->getObjectManager()->getObject(
            Price::class,
            [
                'settingHelper' => $this->settingHelper,
                'filterItemFactory' => $filterItemFactory,
                'groupHelper' => $this->groupHelper,
                'searchEngine' => $searchEngine,
                'messageManager' => $messageManager,
                'priceCurrency' => $priceCurrency,
                'shopbyRequest' => $this->shopbyRequest,
                'scopeConfig' => $this->scopeConfig,
                'coreRegistry' => $this->coreRegistry,
                '_storeManager' => $storeManager,
                'dataProvider' => $this->dataProvider,
            ]
        );

        $this->model->setAttributeModel($attributeModel);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetItemsData()
    {
        $data = [
            'data' =>['count' => 1, 'min' => 1, 'max' => 2],
            '10_20' => ['count' => 2]
        ];
        $settingFilter = $this->getObjectManager()->getObject(FilterSetting::class);
        $settingFilter->setDisplayMode(3);
        $this->settingHelper->expects($this->any())->method('getSettingByLayerFilter')->willReturn($settingFilter);
        $this->coreRegistry->expects($this->any())->method('registry')->willReturn($data);
        $this->assertEquals([], $this->invokeMethod($this->model, '_getItemsData'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetItemsDataWithGoodData()
    {
        $data = [
            'data' =>['count' => 1, 'min' => 1, 'max' => 2],
            '10_20' => ['count' => 2],
            '30_40' => ['count' => 5]
        ];

        $settingFilter = $this->getObjectManager()->getObject(FilterSetting::class);
        $settingFilter->setDisplayMode(3);
        $this->settingHelper->expects($this->any())->method('getSettingByLayerFilter')->willReturn($settingFilter);
        $this->coreRegistry->expects($this->any())->method('registry')->willReturn($data);
        $result = $this->invokeMethod($this->model, '_getItemsData');
        $this->assertEquals(2, count($result));
        $this->assertEquals(2, $result[0]['count']);
        $this->assertEquals(5, $result[1]['count']);
    }

    /**
     * @throws \ReflectionException
     */
    public function testPrepareData()
    {
        $result = $this->invokeMethod($this->model, 'prepareData', ['10_20', 5]);
        $this->assertEquals(5, $result['count']);
        $this->assertEquals(10, $result['from']);
        $this->assertEquals(20, $result['to']);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetAlteredQueryResponse()
    {
        $this->assertNull($this->invokeMethod($this->model, 'getAlteredQueryResponse'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testIsUnical()
    {
        $settingFilter = $this->getObjectManager()->getObject(FilterSetting::class);
        $this->assertFalse($this->invokeMethod($this->model, 'isUnical', [$settingFilter]));
        $settingFilter->setDisplayMode(2);
        $this->assertTrue($this->invokeMethod($this->model, 'isUnical', [$settingFilter]));
        $settingFilter->setDisplayMode(3);
        $this->assertTrue($this->invokeMethod($this->model, 'isUnical', [$settingFilter]));
        $settingFilter->setDisplayMode(1);
        $settingFilter->setAddFromToWidget('1');
        $this->assertTrue($this->invokeMethod($this->model, 'isUnical', [$settingFilter]));
        $settingFilter->setDisplayMode(1);
        $settingFilter->setAddFromToWidget('0');
        $this->assertFalse($this->invokeMethod($this->model, 'isUnical', [$settingFilter]));
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderRangeLabel()
    {
        $this->storeMock->expects($this->any())->method('getCurrentCurrencyRate')->willReturn(1);
        $this->assertEquals(
            '10 - 20',
            (string)$this->invokeMethod($this->model, '_renderRangeLabel', [10, 20])
        );
        $this->assertEquals(
            '10 and above',
            (string)$this->invokeMethod($this->model, '_renderRangeLabel', [10, ''])
        );
        $this->assertEquals(
            '10 - 20',
            (string)$this->invokeMethod($this->model, '_renderRangeLabel', [10, 20])
        );
        $this->dataProvider->expects($this->any())->method('getOnePriceIntervalValue')->willReturn(true);
        $this->assertEquals(
            '10',
            (string)$this->invokeMethod($this->model, '_renderRangeLabel', [10, 10])
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderRangeLabelWithDifferenceRate()
    {
        $this->storeMock->expects($this->any())->method('getCurrentCurrencyRate')->willReturn(2);
        $this->assertEquals(
            '20 - 40',
            (string)$this->invokeMethod($this->model, '_renderRangeLabel', [10, 20])
        );
    }
}
