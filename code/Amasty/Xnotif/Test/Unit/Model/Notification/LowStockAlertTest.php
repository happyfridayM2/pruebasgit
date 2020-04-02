<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Xnotif\Test\Unit\Model\Notification;

use Amasty\Xnotif\Model\Notification\LowStockAlert;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\Xnotif\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Xnotif\Test\Unit\Traits\ReflectionTrait;

/**
 * Class LowStockAlertTest
 *
 * @see LowStockAlert
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LowStockAlertTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectManagerTrait;

    /**
     * @var LowStockAlert|MockObject
     */
    private $plugin;

    /**
     * @var \Amasty\Xnotif\Helper\Config|MockObject
     */
    private $config;

    /**
     * @var array
     */
    private $products = [];

    protected function setUp()
    {
        $this->config = $this->createMock(\Amasty\Xnotif\Helper\Config::class);
        $productRepository = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $typeInstance = $this->createMock(\Magento\Catalog\Model\Product\Type\AbstractType::class);
        $product1 = $this->getObjectManager()->getObject(\Magento\Catalog\Model\Product::class);
        $product2 = $this->getObjectManager()->getObject(\Magento\Catalog\Model\Product::class);
        $this->products[] = $product1;
        $this->products[] = $product2;
        $this->setProperty($product1, '_typeInstance', $typeInstance);
        $this->setProperty($product2, '_typeInstance', $typeInstance);
        $product1->setName('product1')->setSku('product1')->setQty(1);
        $product2->setName('product2')->setSku('product2')->setQty(2);

        $productRepository->expects($this->any())->method('getById')->will($this->onConsecutiveCalls($product1, $product2));
        $typeInstance->expects($this->any())->method('getSku')->will($this->onConsecutiveCalls('product1', 'product1', 'product2', 'product2'));

        $this->plugin = $this->getObjectManager()->getObject(
            LowStockAlert::class,
            [
                'config' => $this->config,
                'productRepository' => $productRepository
            ]
        );
    }

    /**
     * @covers LowStockAlert::getEmailTo
     * @throws \ReflectionException
     */
    public function testGetEmailTo()
    {
        $this->config->expects($this->any())->method('getModuleConfig')->will($this->onConsecutiveCalls('test', 'test1,test2'));

        $this->assertEquals('test', $this->invokeMethod($this->plugin, 'getEmailTo'));
        $this->assertEquals(['test2'], $this->invokeMethod($this->plugin, 'getEmailTo'));
    }

    /**
     * @covers LowStockAlert::getLowStockItems
     * @throws \ReflectionException
     */
    public function testGetLowStockItems()
    {
        $result = [
            ['name' => 'product1', 'sku' =>'product1', 'qty' => 1.0],
            ['name' => 'product2', 'sku' =>'product2', 'qty' => 2.0]
        ];
        $this->config->expects($this->any())->method('getQuantityBelow')->will($this->onConsecutiveCalls(5, 3));

        $this->assertEquals([], $this->invokeMethod($this->plugin, 'getLowStockItems', [[], 1]));
        $this->assertEquals($result, $this->invokeMethod($this->plugin, 'getLowStockItems', [$this->products, 1]));
    }
}
