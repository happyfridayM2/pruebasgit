<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Test\Unit\Block\Adminhtml\Page;

use Amasty\ShopbyPage\Block\Adminhtml\Page\Selection;
use Amasty\ShopbyPage\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class SelectionTest
 */
class SelectionTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;

    const TEST_COUNTER = ['test1', 'test2', 'test3', 'test4', 'test5'];

    /**
     * @var Selection
     */
    private $block;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\ShopbyPage\Model\Page
     */
    private $model;

    public function setUp()
    {
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);
        $this->model = $this->getMockBuilder(\Amasty\ShopbyPage\Model\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConditions'])
            ->getMock();
        $this->registry->expects($this->once())
            ->method('registry')
            ->will($this->returnValue($this->model));

        $this->block = $this->getObjectManager()->getObject(
            Selection::class,
            [
                '_coreRegistry' => $this->registry
            ]
        );
    }

    /**
     * @covers Selection::getCounter
     *
     * @throws \ReflectionException
     */
    public function testGetCounter()
    {
        $this->model->expects($this->any())
            ->method('getConditions')
            ->will($this->returnValue(self::TEST_COUNTER));
        $this->assertEquals(count(self::TEST_COUNTER), $this->block->getCounter());
    }
}
