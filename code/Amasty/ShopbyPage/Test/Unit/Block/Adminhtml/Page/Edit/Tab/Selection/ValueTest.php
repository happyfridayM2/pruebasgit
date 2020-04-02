<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Test\Unit\Block\Adminhtml\Page\Edit\Tab\Selection;

use Amasty\ShopbyPage\Block\Adminhtml\Page\Edit\Tab\Selection\Value;
use Amasty\ShopbyPage\Test\Unit\Traits;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Registry;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ValueTest
 *
 * @see Value
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ValueTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Value
     */
    private $block;

    /**
     * @var Registry
     */
    private $coreRegistry;

    public function setUp()
    {
        $this->coreRegistry = $this->createMock(Registry::class);

        $this->block = $this->getObjectManager()->getObject(
            Value::class,
            [
                '_coreRegistry' => $this->coreRegistry
            ]
        );
    }

    /**
     * @covers Value::getEavAttribute
     */
    public function testGetEavAttribute()
    {
        $this->coreRegistry->expects($this->any())->method('registry')->willReturn(1);
        $this->assertEquals(1, $this->block->getEavAttribute());
        $this->setProperty($this->block, '_eavAttribute', 2);
        $this->assertEquals(2, $this->block->getEavAttribute());
    }

    /**
     * @covers Value::getInputName
     */
    public function testGetInputName()
    {
        $this->setProperty($this->block, '_attributeIdx', 'test');
        $this->assertEquals('conditions[test][value]', $this->block->getInputName());
    }

    /**
     * @covers Value::getEavAttributeIdx
     */
    public function testGetEavAttributeIdx()
    {
        $this->coreRegistry->expects($this->any())->method('registry')->willReturn(1);
        $this->assertEquals(1, $this->block->getEavAttribute());
        $this->setProperty($this->block, '_attributeIdx', 2);
        $this->assertEquals(2, $this->block->getEavAttributeIdx());
    }

    /**
     * @covers Value::getFrontendInput
     */
    public function testGetFrontendInput()
    {
        $this->coreRegistry->expects($this->any())->method('registry')->willReturn(null);
        $attribute = $this->createMock(AbstractAttribute::class);
        $attribute->expects($this->any())->method('getFrontendInput')->willReturn(true);
        $this->assertNull($this->block->getFrontendInput());
        $this->setProperty($this->block, '_eavAttribute', $attribute);
        $this->assertTrue($this->block->getFrontendInput());
    }
}
