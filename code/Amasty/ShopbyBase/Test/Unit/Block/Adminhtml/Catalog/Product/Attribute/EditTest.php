<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Test\Unit\Block\Adminhtml\Catalog\Product\Attribute;

use Amasty\ShopbyBase\Block\Adminhtml\Catalog\Product\Attribute\Edit;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\ShopbyBase\Test\Unit\Traits;

/**
 * Class EditTest
 *
 * @see Edit
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;

    /**
     * @var \Magento\Framework\Registry|MockObject
     */
    private $registry;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    private $attribute;

    /**
     * @var Edit
     */
    private $block;

    /**
     * @var \Amasty\ShopbyBase\Model\FilterSetting\AttributeConfig|MockObject
     */
    private $attributeConfig;

    protected function setUp()
    {
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);
        $displayModeSource = $this->createMock(\Amasty\ShopbyBase\Model\Source\DisplayMode::class);
        $this->attributeConfig = $this->createMock(\Amasty\ShopbyBase\Model\FilterSetting\AttributeConfig::class);
        $this->attribute = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $this->attribute->expects($this->once())->method('getAttributeCode')->will($this->returnValue('attribute'));
        $this->registry->expects($this->once())
            ->method('registry')
            ->will($this->returnValue($this->attribute));

        $this->block = $this->getObjectManager()->getObject(
            Edit::class,
            [
                'coreRegistry' => $this->registry,
                'displayModeSource' => $displayModeSource,
                'attributeConfig' => $this->attributeConfig
            ]
        );
    }

    /**
     * @covers Edit::getFilterCode
     */
    public function testGetFilterCode()
    {
        $this->assertEquals('attr_attribute', $this->block->getFilterCode());
    }

    /**
     * @covers Edit::canConfigureAttributeOptions
     */
    public function testCanConfigureAttributeOptionsIfTrue()
    {
        $this->attributeConfig->expects($this->once())
            ->method('canBeConfigured')
            ->will($this->returnValue(true));

        $this->assertTrue($this->block->canConfigureAttributeOptions());
    }

    /**
     * @covers Edit::canConfigureAttributeOptions
     */
    public function testCanConfigureAttributeOptionsIfFalse()
    {
        $this->attributeConfig->expects($this->once())
            ->method('canBeConfigured')
            ->will($this->returnValue(false));

        $this->assertFalse($this->block->canConfigureAttributeOptions());
    }
}
