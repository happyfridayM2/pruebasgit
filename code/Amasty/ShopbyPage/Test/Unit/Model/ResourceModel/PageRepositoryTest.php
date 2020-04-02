<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Test\Unit\Model\ResourceModel;

use Amasty\ShopbyPage\Model\ResourceModel\PageRepository;
use Amasty\ShopbyPage\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PageRepositoryTest
 *
 * @see PageRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class PageRepositoryTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var PageRepository
     */
    private $model;

    public function setUp()
    {
        $serializer = $this->createMock(\Amasty\Base\Model\Serializer::class);
        $pageFactory = $this->createMock(\Amasty\ShopbyPage\Model\PageFactory::class);
        $pageResourceModel = $this->createMock(\Amasty\ShopbyPage\Model\ResourceModel\Page::class);
        $page = $this->createMock(\Magento\Framework\Model\AbstractModel::class);
        $serializer->expects($this->any())->method('serialize')->with([1, 2, 3])->willReturn('a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}');
        $serializer->expects($this->any())->method('unserialize')
            ->withConsecutive(['a:3:{i:0;i:a;i:1;i:b;i:2;i:c;}'], ['s:1:"a";'], ['s:1:"b";'], ['s:1:"c";'])
            ->willReturnOnConsecutiveCalls(['s:1:"a";', 's:1:"b";', 's:1:"c";'], 'a', 'b', 'c');

        $pageFactory->expects($this->any())->method('create')->willReturn($page);
        $pageResourceModel->expects($this->any())->method('load')->willReturn($page);

        $this->model = $this->getObjectManager()->getObject(
            PageRepository::class,
            [
                'serializer' => $serializer,
                'pageFactory' => $pageFactory,
                'pageResourceModel' => $pageResourceModel
            ]
        );
    }

    /**
     * @covers PageRepository::implodeMultipleData
     */
    public function testImplodeMultipleData()
    {
        $data = [];
        $this->invokeMethod($this->model, 'implodeMultipleData', [&$data, 'test', '-']);
        $this->assertEquals(['test' => null], $data);
        $data = ['test' => [1, 2, 3]];
        $this->invokeMethod($this->model, 'implodeMultipleData', [&$data, 'test', '-']);
        $this->assertEquals(['test' => '1-2-3'], $data);
    }

    /**
     * @covers PageRepository::serializeMultipleData
     */
    public function testSerializeMultipleData()
    {
        $data = [];
        $this->invokeMethod($this->model, 'serializeMultipleData', [&$data, 'test']);
        $this->assertEquals(['test' => null], $data);
        $data = ['test' => [1, 2, 3]];
        $this->invokeMethod($this->model, 'serializeMultipleData', [&$data, 'test']);
        $this->assertEquals(['test' => 'a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}'], $data);
    }

    /**
     * @covers PageRepository::normalizeOutputData
     */
    public function testNormalizeOutputData()
    {
        $data = [
            'top_block_id' => '',
            'bottom_block_id' => '',
        ];
        $result = [
            'top_block_id' => null,
            'bottom_block_id' => null,
            'categories' => null,
            'conditions' => null
        ];
        $this->invokeMethod($this->model, 'normalizeOutputData', [&$data]);
        $this->assertEquals($result, $data);
    }

    /**
     * @covers PageRepository::normalizeInputData
     */
    public function testNormalizeInputData()
    {
        $data = [
            'categories' => '',
            'store_id' => '',
            'conditions' => ''
        ];
        $result = [
            'store_id' => '',
            'stores' => '',
            'categories' => '',
            'conditions' => []
        ];
        $this->invokeMethod($this->model, 'normalizeInputData', [&$data]);
        $this->assertEquals($result, $data);
        $data = [
            'categories' => '1,2',
            'store_id' => 1,
            'conditions' => ''
        ];
        $result = [
            'store_id' => 1,
            'stores' => 1,
            'categories' => [1, 2],
            'conditions' => []
        ];
        $this->invokeMethod($this->model, 'normalizeInputData', [&$data]);
        $this->assertEquals($result, $data);
    }

    /**
     * @covers PageRepository::processCategoryField
     */
    public function testProcessCategoryField()
    {
        $data = '';
        $this->invokeMethod($this->model, 'processCategoryField', [&$data]);
        $this->assertEquals('', $data);

        $data = '1, 2';
        $this->invokeMethod($this->model, 'processCategoryField', [&$data]);
        $this->assertEquals([1, 2], $data);
    }

    /**
     * @covers PageRepository::processConditionsField
     */
    public function testProcessConditionsField()
    {
        $data = '';
        $this->invokeMethod($this->model, 'processConditionsField', [&$data]);
        $this->assertEquals([], $data);

        $data = 'a:3:{i:0;i:a;i:1;i:b;i:2;i:c;}';
        $this->invokeMethod($this->model, 'processConditionsField', [&$data]);
        $this->assertEquals(['a', 'b', 'c'], $data);
    }

    /**
     * @covers PageRepository::processConditionsField
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGet()
    {
        $this->model->get(1);
    }
}
