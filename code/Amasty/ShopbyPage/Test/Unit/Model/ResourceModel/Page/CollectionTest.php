<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Test\Unit\Model\ResourceModel\Page;

use Amasty\ShopbyPage\Model\ResourceModel\Page\Collection;
use Amasty\ShopbyBase\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class CollectionTest
 *
 * @see Collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers Collection::getStoreDataForItem
     * @dataProvider preparedDataForItem
     *
     * @param array $data
     * @param array $expected
     *
     * @throws \ReflectionException
     */
    public function testGetStoreDataForItem($data, $expected)
    {
        /** @var \Magento\Store\Model\Store|MockObject $store */
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getCode', 'getId']);
        $store->expects($this->any())->method('getId')->willReturn(1);
        $store->expects($this->any())->method('getCode')->willReturn('default');

        /** @var \Magento\Store\Model\StoreManager|MockObject $storeManager */
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);
        $storeManager->expects($this->any())->method('getStores')->willReturn([ 'default' => $store ]);

        $collection = $this->createPartialMock(Collection::class, []);
        $this->setProperty($collection, '_storeManager', $storeManager, Collection::class);

        $result = $this->invokeMethod($collection, 'getStoreDataForItem', [$data]);

        $this->assertEquals($expected, $result);
    }

    public function preparedDataForItem()
    {
        return [
            [[], [false, 'default']],
            [['1' => '1', '0' => '0'], ['1', 'default']],
            [['1' => '1', 0 => 0], [1, 'default']],
        ];
    }
}
