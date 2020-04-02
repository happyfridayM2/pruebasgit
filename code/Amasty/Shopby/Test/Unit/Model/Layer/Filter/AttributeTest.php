<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Test\Unit\Model\Layer\Filter;

use Amasty\Shopby\Helper\Group as GroupHelper;
use Amasty\Shopby\Model\GroupAttr\DataProvider as ShopbyDataProvider;
use Amasty\Shopby\Model\Layer\Filter\Attribute;
use Amasty\Shopby\Test\Unit\Traits;
use Amasty\ShopbyBase\Model\FilterSetting;
use Magento\Framework\Search\Request;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class BrandsPopupTest
 *
 * @see Attribute
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const GROUP_CODE = 'grCode';

    const OPTIONS_ARRAY = [
        8  => ['value' => '8' , 'count' => '6'],
        11 => ['value' => '11', 'count' => '9'],
        18 => ['value' => '18', 'count' => '4'],
        19 => ['value' => '19', 'count' => '5'],
        20 => ['value' => '20', 'count' => '6'],
        21 => ['value' => '21', 'count' => '3'],
        22 => ['value' => '22', 'count' => '6'],
        23 => ['value' => '23', 'count' => '5'],
    ];

    const GROUP_ATTR_1__DATA = [
        'group_id' => '1',
        'group_code' => self::GROUP_CODE,
    ];

    const GROUP_ATTR_10__DATA = [
        'group_id' => '10',
        'group_code' => self::GROUP_CODE,
    ];

    /**
     * @covers Attribute::adjustFacetedDataToGroup
     *
     * @dataProvider prepareTestDataFoAdjustToGroup
     *
     * @param array $optionsFacetedData
     * @param array $expectedResult
     * @param int $attrId
     *
     * @throws \ReflectionException
     */
    public function testAdjustFacetedDataToGroup($optionsFacetedData, $expectedResult, $attrId = 0)
    {
        $groupHelper = $this->createPartialMock(GroupHelper::class, ['getGroupAttributeDataProvider']);

        /** @var ShopbyDataProvider|MockObject $groupDataProvider */
        $groupDataProvider = $this->createMock(ShopbyDataProvider::class);
        $groupDataProvider
            ->expects($this->any())
            ->method('getGroupsByAttributeId')
            ->willReturnCallback(
                function ($attributeId) {
                    if ($attributeId === 0) {
                        return [];
                    }
                    $groupAttr = $this->getObjectManager()->getObject(\Amasty\Shopby\Model\GroupAttr::class);

                    if ($attributeId === 1) {
                        $groupAttr->setData(static::GROUP_ATTR_1__DATA);
                    } elseif ($attributeId === 10) {
                        $groupAttr->setData(static::GROUP_ATTR_10__DATA);
                    }

                    return [$groupAttr];
                }
            );

        $groupHelper->expects($this->any())->method('getGroupAttributeDataProvider')->willReturn($groupDataProvider);

        /** @var Attribute $model */
        $model = $this->getObjectManager()->getObject(Attribute::class, ['groupHelper' => $groupHelper]);

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|MockObject $attributeModel */
        $attributeModel = $this->createPartialMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class, []);

        $model->setData('attribute_model', $attributeModel->setId($attrId));
        $actualResult = $this->invokeMethod($model, 'adjustFacetedDataToGroup', [$optionsFacetedData]);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function prepareTestDataFoAdjustToGroup()
    {
        return [
            [[], []],
            [
                static::OPTIONS_ARRAY,
                static::OPTIONS_ARRAY
            ],
            [
                static::OPTIONS_ARRAY,
                static::OPTIONS_ARRAY,
                1
            ],
            [
                static::OPTIONS_ARRAY + [
                    GroupHelper::LAST_POSSIBLE_OPTION_ID - 10 => [
                        'value' => GroupHelper::LAST_POSSIBLE_OPTION_ID - 10,
                        'count' => '7',
                    ]
                ],
                static::OPTIONS_ARRAY + [
                    static::GROUP_CODE => [
                        'value' => GroupHelper::LAST_POSSIBLE_OPTION_ID - 10,
                        'count' => '7',
                    ]
                ],
                10
            ],
        ];
    }

    /**
     * @covers Attribute::getAlteredQueryResponse
     *
     * @throws \ReflectionException
     */
    public function testGetAlteredQueryResponse()
    {
        /** @var Attribute|MockObject $model */
        $model = $this->createPartialMock(Attribute::class, ['getRequestBuilder']);
        $this->assertNull($this->invokeMethod($model, 'getAlteredQueryResponse'));

        $settingFilter = $this->getObjectManager()->getObject(FilterSetting::class);
        $settingHelper = $this->createMock(\Amasty\Shopby\Helper\FilterSetting::class);
        $settingHelper->expects($this->any())->method('getSettingByLayerFilter')->willReturn($settingFilter);

        $requestBuilder = $this->createPartialMock(\Amasty\Shopby\Model\Request\Builder::class, ['create']);
        $requestBuilder->expects($this->any())->method('create')->willReturnCallback(
            function () {
                return $this->getObjectManager()->getObject(Request::class);
            }
        );
        $model->expects($this->any())->method('getRequestBuilder')->willReturn($requestBuilder);

        $searchEngine = $this->createPartialMock(\Magento\Search\Model\SearchEngine::class, ['search']);
        $searchEngine->expects($this->any())->method('search')->willReturnArgument(0);

        $this->setProperty($model, 'settingHelper', $settingHelper, Attribute::class);
        $this->setProperty($model, 'searchEngine', $searchEngine, Attribute::class);
        $this->setProperty($model, 'currentValue', 'test');

        $this->assertInstanceOf(Request::class, $this->invokeMethod($model, 'getAlteredQueryResponse'));
    }
}
