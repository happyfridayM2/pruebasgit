<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Test\Unit\Model;

use Amasty\ShopbyBrand\Model\ProductCount;
use Amasty\ShopbyBrand\Test\Unit\Traits;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ProductCountTest
 *
 * @see ProductCount
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ProductCountTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var ProductCount
     */
    private $model;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $brandHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoreInterface
     */
    private $storeMock;

    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
     */
    private $collection;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    public function setUp()
    {
        $this->brandHelper = $this->createMock(\Amasty\ShopbyBrand\Helper\Data::class);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->collection = $this->createMock(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::class);
        $this->categoryRepository = $this->createMock(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getRootCategoryId'])
            ->getMockForAbstractClass();

        $this->model = $this->getObjectManager()->getObject(
            ProductCount::class,
            [
                'brandHelper' => $this->brandHelper,
                'storeManager' => $this->storeManager,
                'collection' => $this->collection,
                'categoryRepository' => $this->categoryRepository,
            ]
        );
    }

    /**
     * @covers ProductCount::get
     */
    public function testGet()
    {
        $this->brandHelper->expects($this->any())->method('getBrandAttributeCode')->willReturn('test');
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $categoryMock = $this->createMock(\Magento\Catalog\Model\Category::class);
        $this->categoryRepository->expects($this->any())->method('get')->willReturn($categoryMock);
        $this->collection->expects($this->any())->method('addAttributeToSelect')->willReturn($this->collection);
        $this->collection->expects($this->any())->method('setVisibility')->willReturn($this->collection);
        $this->collection->expects($this->any())->method('addCategoryFilter')->willReturn($this->collection);
        $this->collection->expects($this->any())->method('getFacetedData')->willReturn([['count' => 5]]);
        $this->assertEquals(5, $this->model->get(0));

        $this->setProperty($this->model, 'productCount', []);
        $this->assertEquals(0, $this->model->get(1));
    }
}
