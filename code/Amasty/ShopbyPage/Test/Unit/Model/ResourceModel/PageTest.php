<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Test\Unit\Model\ResourceModel;

use Amasty\ShopbyPage\Model\ResourceModel\Page;
use Amasty\ShopbyPage\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PageTest
 *
 * @see Page
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class PageTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Page
     */
    private $model;

    public function setUp()
    {
        $this->model = $this->getMockBuilder(Page::class)
            ->setMethods(['lookupStoreIds', 'getConnection', 'getTable'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model->expects($this->any())->method('getTable')->willReturn('test');
    }

    /**
     * @covers Category::saveStores
     */
    public function testSaveStoresWithDelete()
    {
        $this->model->expects($this->any())->method('lookupStoreIds')->willReturn([1, 2]);
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->model->expects($this->any())->method('getConnection')->willReturn($connection);
        $connection->expects($this->once())->method('delete')->willReturn(true);

        $object = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->setMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $object->expects($this->any())->method('getStoreId')->willReturn(2);
        $this->model->saveStores($object);
    }

    /**
     * @covers Category::saveStores
     */
    public function testSaveStoresWithInserte()
    {
        $this->model->expects($this->any())->method('lookupStoreIds')->willReturn([1]);
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->model->expects($this->any())->method('getConnection')->willReturn($connection);
        $connection->expects($this->once())->method('insertMultiple')->willReturn(false);
        $object = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->setMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $object->expects($this->any())->method('getStoreId')->willReturn([1, 2]);
        $this->model->saveStores($object);
    }

    /**
     * @covers Category::resolveStoresInfo
     */
    public function testResolveStoresInfo()
    {
        $object = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->setMethods(['getStoreId', 'getStores'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $object->expects($this->any())->method('getStores')->will($this->onConsecutiveCalls([], [2]));
        $object->expects($this->any())->method('getStoreId')->willReturn(1);
        $this->assertEquals([null, [1]], $this->invokeMethod($this->model, 'resolveStoresInfo', [$object]));
        $this->assertEquals([null, [2]], $this->invokeMethod($this->model, 'resolveStoresInfo', [$object]));
    }
}
