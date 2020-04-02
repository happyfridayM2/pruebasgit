<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Test\Unit\Controller\Adminhtml\Page;

use Amasty\ShopbyPage\Controller\Adminhtml\Page\AddSelection;
use Amasty\ShopbyPage\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class AddSelectionTest
 *
 * @see AddSelection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class AddSelectionTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var AddSelection
     */
    private $controller;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    private $attribute;

    public function setUp()
    {
        $catalogConfig = $this->createMock(\Magento\Catalog\Model\Config::class);
        $context = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->attribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);

        $context->expects($this->any())->method('getRequest')->willReturn($request);
        $request->expects($this->any())->method('getParam')->willReturn(1);
        $catalogConfig->expects($this->any())->method('getAttribute')->willReturn($this->attribute);

        $this->controller = $this->getObjectManager()->getObject(
            AddSelection::class,
            [
                'context' => $context,
                '_catalogConfig' => $catalogConfig,
            ]
        );
    }

    /**
     * @covers AddSelection::loadAttribute
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testLoadAttribute()
    {
        $this->attribute->expects($this->any())->method('getId')->will($this->onConsecutiveCalls(1, 0));
        $this->assertEquals($this->attribute, $this->invokeMethod($this->controller, 'loadAttribute'));
        $this->invokeMethod($this->controller, 'loadAttribute');
    }
}
