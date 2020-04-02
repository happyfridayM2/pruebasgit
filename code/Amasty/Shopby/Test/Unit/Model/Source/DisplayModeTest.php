<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Test\Model\Source;

use Amasty\Shopby\Test\Unit\Traits;

/**
 * Class DisplayModeTest
 * @package Amasty\Shopby\Test\Model\Source
 */
class DisplayModeTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var \Amasty\Shopby\Model\Source\DisplayMode
     */
    private $model;

    /**
     * @var \Amasty\Shopby\Model\Source\DisplayMode
     */
    private $attribute;

    public function setUp()
    {
        $this->model = $this->getObjectManager()->getObject(\Amasty\Shopby\Model\Source\DisplayMode::class, []);
        $this->attribute = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['getId', 'getFrontendInput']
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testShowSwatchOptionsWithoutAttribute()
    {
        $this->assertFalse($this->invokeMethod($this->model, 'showSwatchOptions'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testShowSwatchOptionsWithoutId()
    {
        $this->attribute->method('getId')->willReturn(0);
        $this->model->setAttribute($this->attribute);

        $this->assertFalse($this->invokeMethod($this->model, 'showSwatchOptions'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testShowSwatchOptions()
    {
        $this->attribute->method('getId')->willReturn(1);
        $this->attribute->method('getFrontendInput')->will($this->onConsecutiveCalls('select', 'multiselect', 'price'));
        $this->model->setAttribute($this->attribute);

        $this->assertTrue($this->invokeMethod($this->model, 'showSwatchOptions'));
        $this->assertTrue($this->invokeMethod($this->model, 'showSwatchOptions'));
        $this->assertFalse($this->invokeMethod($this->model, 'showSwatchOptions'));
    }
}
