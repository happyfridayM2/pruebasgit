<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


use Amasty\ShopbySeo\Plugin\Adminhtml\ConfigPlugin;
use Amasty\ShopbySeo\Test\Unit\Traits;

/**
 * Class ConfigPlugin
 *
 * @see ConfigPlugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ConfigPluginTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const GROUPS_TEST_WITHOUT_VALUE = [
        'url' => [
            'fields' => [
                'mode' => [
                    'value' => null
                ]
            ]
        ]
    ];

    const GROUPS_TEST_WITH_VALUE = [
        'url' => [
            'fields' => [
                'mode' => [
                    'value' => 'test'
                ],
                'filter_word' => [
                    'value' => 'Test-Value'
                ]
            ]
        ]
    ];

    /**
     * @covers ConfigPlugin::beforeSave
     * @dataProvider beforeSaveDataProvider
     */
    public function testBeforeSave($groups, $section, $expected, $isModuleEnable)
    {
        $configPlugin = $this->getMockBuilder(ConfigPlugin::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $filter = $this->createPartialMock(
            \Magento\Framework\Filter\FilterManager::class,
            ['translitUrl']
        );
        $moduleHelper = $this->createMock(\Amasty\ShopbySeo\Helper\Data::class);
        $filter->expects($this->any())->method('translitUrl')->willReturn('test-value');
        $moduleHelper->expects($this->any())->method('isModuleEnabled')->willReturn($isModuleEnable);
        $this->setProperty($configPlugin, 'filter', $filter, ConfigPlugin::class);
        $this->setProperty($configPlugin, 'moduleHelper', $moduleHelper, ConfigPlugin::class);

        $config = $this->getObjectManager()->getObject(\Magento\Config\Model\Config::class);
        $config->setData(
            ['groups' => $groups, 'section' => $section]
        );

        $result = $configPlugin->beforeSave($config);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider fot beforeSave test
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        $resultGroup = self::GROUPS_TEST_WITH_VALUE;
        $resultGroup['url']['fields']['filter_word']['value'] = 'test_value';

        return [
            [self::GROUPS_TEST_WITHOUT_VALUE, 'amasty_shopby_base', self::GROUPS_TEST_WITHOUT_VALUE, false],
            [self::GROUPS_TEST_WITHOUT_VALUE, 'amasty_shopby_seo', self::GROUPS_TEST_WITHOUT_VALUE, false],
            [self::GROUPS_TEST_WITH_VALUE, 'amasty_shopby_seo', $resultGroup, true]
        ];
    }
}
