<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Test\Unit\Model\FilterSetting;

use Amasty\ShopbyBase\Model\FilterSetting\AttributeConfig;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\ShopbyBase\Test\Unit\Traits;

/**
 * Class AttributeConfigTest
 *
 * @see AttributeConfig
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class AttributeConfigTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ReflectionTrait;
    use Traits\ObjectManagerTrait;

    /**
     * @var AttributeConfig
     */
    private $model;

    protected function setUp()
    {
        /** @var \Amasty\ShopbyBrand\Model\FilterSetting\AttributeListProvider|MockObject $provider */
        $provider = $this->createPartialMock(
            \Amasty\ShopbyBrand\Model\FilterSetting\AttributeListProvider::class,
            ['getAttributeList']
        );
        $provider->expects($this->once())->method('getAttributeList')->willReturn(
            ['attr_1' => true, 'attr_2' => false]
        );

        $this->model =
            $this->getObjectManager()->getObject(AttributeConfig::class, ['attributeProviders' => [$provider]]);
    }

    /**
     * @covers AttributeConfig::canBeConfigured
     *
     * @throws \ReflectionException
     */
    public function testCanBeConfigured()
    {
        $this->assertFalse($this->model->canBeConfigured('attr_2'));
        $this->assertTrue($this->model->canBeConfigured('attr_1'));

        $this->setProperty($this->model, 'attributeList', [AttributeConfig::ALL_ATTRIBUTES_PARAM => true]);

        $this->assertTrue($this->model->canBeConfigured('attr_2'));
    }
}
