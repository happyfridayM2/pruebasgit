<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


use Amasty\ShopbySeo\Observer\Admin\OptionFormBuildAfter;
use Amasty\ShopbySeo\Test\Unit\Traits;

/**
 * Class OptionFormBuildAfter
 *
 * @see OptionFormBuildAfter
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class OptionFormBuildAfterTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const FILTER_CODE = 'test_code';

    /**
     * @covers OptionFormBuildAfter::isSeoURLEnabled
     * @dataProvider dataProvider
     */
    public function testIsSeoURLEnabled($id, $seoSign, $expected)
    {
        $filterSetting = $this->getObjectManager()
            ->getObject(\Amasty\ShopbyBase\Model\FilterSetting::class);
        $filterSetting->setData(
            [
                'setting_id' => $id,
                'is_seo_significant' => $seoSign
            ]
        );

        $filterSettingHelper = $this->createMock(\Amasty\ShopbyBase\Helper\FilterSetting::class);
        $filterSettingHelper->expects($this->any())->method('getSettingByAttributeCode')
            ->with(self::FILTER_CODE)
            ->willReturn($filterSetting);

        $optionSetting = $this->createMock(\Amasty\ShopbyBase\Api\Data\OptionSettingInterface::class);
        $optionSetting->expects($this->any())->method('getFilterCode')->willReturn(self::FILTER_CODE);

        $optionForm = $this->getObjectManager()
            ->getObject(
                OptionFormBuildAfter::class,
                [
                    'model' => $optionSetting,
                    'filterSettingHelper' => $filterSettingHelper
                ]
            );

        $result = $this->invokeMethod($optionForm, 'isSeoURLEnabled');
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for isSeoUrlEnalbed test
     * @return array
     */
    public function dataProvider()
    {
        return [
            [1, true, true],
            [1, false, false],
            [0, true, false]
        ];
    }
}
