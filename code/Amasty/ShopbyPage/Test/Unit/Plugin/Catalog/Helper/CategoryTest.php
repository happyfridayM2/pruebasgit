<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Test\Unit\Model;

use Amasty\ShopbyPage\Plugin\Catalog\Helper\Category;
use Amasty\ShopbyPage\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class CategoryTest
 *
 * @see Category
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Category
     */
    private $plugin;

    public function setUp()
    {
        $layerResolver = $this->createMock(\Magento\Catalog\Model\Layer\Resolver::class);
        $layer = $this->createMock(\Magento\Catalog\Model\Layer::class);
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);

        $layerResolver->expects($this->any())->method('get')->will($this->onConsecutiveCalls(null, $layer));
        $layer->expects($this->any())->method('getCurrentCategory')->willReturn($category);
        $category->expects($this->any())->method('getData')->willReturn(true);

        $this->plugin = $this->getObjectManager()->getObject(
            Category::class,
            [
                'layerResolver' => $layerResolver
            ]
        );
    }

    /**
     * @covers Category::getCurrentCategory
     */
    public function testGetCurrentCategory()
    {
        $this->assertNull($this->invokeMethod($this->plugin, 'getCurrentCategory'));
        $this->assertNotNull($this->invokeMethod($this->plugin, 'getCurrentCategory'));
    }

    /**
     * @covers Category::afterCanUseCanonicalTag
     */
    public function testAfterCanUseCanonicalTag()
    {
        $category = $this->createMock(\Magento\Catalog\Helper\Category::class);
        $this->assertTrue($this->plugin->afterCanUseCanonicalTag($category, true));
        $this->assertTrue($this->plugin->afterCanUseCanonicalTag($category, false));
    }
}
