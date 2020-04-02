<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Test\Unit\Helper;

use Amasty\ShopbyBase\Helper\FilterSetting;
use Amasty\ShopbyBrand\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\ShopbyBrand\Test\Unit\Traits\ReflectionTrait;

/**
 * Class FilterSettingTest
 *
 * @see FilterSetting
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class FilterSettingTest extends \PHPUnit\Framework\TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var FilterSetting
     */
    private $helper;

    public function setUp()
    {
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $scopeConfig->expects($this->any())->method('getValue')->willReturn(1);

        $this->helper = $this->getObjectManager()->getObject(
            FilterSetting::class,
            [
                'scopeConfig' => $scopeConfig
            ]
        );
    }

    /**
     * @covers FilterSetting::getFilterCode
     */
    public function testGetFilterCode()
    {
        $layerFilter = $this->getObjectManager()->getObject(\Amasty\Shopby\Model\Layer\Filter\Attribute::class);
        $this->assertNull($this->invokeMethod($this->helper, 'getFilterCode', [$layerFilter]));
        $layerFilter->setFilterCode('code');
        $layerFilter->setSetting('test');
        $this->assertNull($this->invokeMethod($this->helper, 'getFilterCode', [$layerFilter]));
        $layerFilter->setSetting($layerFilter);
        $this->assertEquals('code', $this->invokeMethod($this->helper, 'getFilterCode', [$layerFilter]));

        $layerFilter->setData('attribute_model', 'test');
        $this->assertNull($this->invokeMethod($this->helper, 'getFilterCode', [$layerFilter]));
        $layerFilter->setAttributeCode('code');
        $layerFilter->setData('attribute_model', $layerFilter);
        $this->assertEquals('attr_code', $this->invokeMethod($this->helper, 'getFilterCode', [$layerFilter]));
    }

    /**
     * @covers FilterSetting::getCustomDataForCategoryFilter
     */
    public function testGetCustomDataForCategoryFilter()
    {
        $result = $this->helper->getCustomDataForCategoryFilter();
        $this->assertArrayHasKey('category_tree_depth', $result);
        $this->assertArrayHasKey('subcategories_view', $result);
        $this->assertArrayHasKey('subcategories_expand', $result);
        $this->assertArrayHasKey('render_all_categories_tree', $result);
        $this->assertArrayHasKey('render_categories_level', $result);
    }
}
