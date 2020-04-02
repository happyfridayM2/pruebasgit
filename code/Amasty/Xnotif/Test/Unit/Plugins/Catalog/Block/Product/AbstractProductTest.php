<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Xnotif\Test\Unit\Plugins\Catalog\Block\Product;

use Amasty\Xnotif\Plugins\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\AbstractProduct as ProductBlock;
use Magento\Framework\View\LayoutInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\Xnotif\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Xnotif\Test\Unit\Traits\ReflectionTrait;

/**
 * Class AbstractProductTest
 *
 * @see AbstractProduct
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractProductTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectManagerTrait;

    /**
     * @var AbstractProduct|MockObject
     */
    private $plugin;

    /**
     * @var \Magento\Catalog\Block\Product\AbstractProduct
     */
    private $productBlock;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $productModel;

    protected function setUp()
    {
        $this->productBlock = $this->getObjectManager()->getObject(\Magento\Catalog\Block\Product\AbstractProduct::class);
        $this->productModel = $this->getObjectManager()->getObject(\Magento\Catalog\Model\Product::class);
        $this->plugin = $this->createPartialMock(
            AbstractProduct::class,
            ['enableSubscribe', 'getSubscribeHtml']
        );
    }

    /**
     * @covers AbstractProduct::beforeGetProductDetailsHtml
     * @throws \ReflectionException
     */
    public function testBeforeGetProductDetailsHtml()
    {
        $this->assertEquals(
            [$this->productModel],
            $this->plugin->beforeGetProductDetailsHtml($this->productBlock, $this->productModel)
        );
        $this->assertEquals($this->productModel, $this->plugin->getProduct());
    }

    /**
     * @covers AbstractProduct::afterGetProductDetailsHtml
     * @throws \ReflectionException
     */
    public function testAfterGetProductDetailsHtml()
    {
        $this->plugin->expects($this->any())->method('enableSubscribe')->will($this->onConsecutiveCalls(false, true));
        $this->plugin->expects($this->any())->method('getSubscribeHtml')->willReturn('test1');
        $this->assertEquals('test', $this->plugin->afterGetProductDetailsHtml($this->productBlock, 'test'));
        $this->assertEquals('testtest1', $this->plugin->afterGetProductDetailsHtml($this->productBlock, 'test'));
    }

    /**
     * @covers AbstractProduct::beforeGetReviewsSummaryHtml
     * @throws \ReflectionException
     */
    public function testBeforeGetReviewsSummaryHtml()
    {
        $this->assertEquals(
            [$this->productModel, false, false],
            $this->plugin->beforeGetReviewsSummaryHtml($this->productBlock, $this->productModel)
        );
        $this->assertEquals($this->productModel, $this->plugin->getProduct());
    }

    /**
     * @covers AbstractProduct::beforeGetReviewsSummaryHtml
     * @throws \ReflectionException
     */
    public function testAfterGetReviewsSummaryHtml()
    {
        $this->plugin->expects($this->any())->method('enableSubscribe')->willReturn(true);
        $this->plugin->expects($this->any())->method('getSubscribeHtml')->willReturn('test1');
        $this->setProperty($this->productBlock, '_nameInLayout', 'category.products.list');
        $this->assertEquals(
            'test',
            $this->plugin->afterGetReviewsSummaryHtml($this->productBlock, 'test')
        );

        $this->setProperty($this->productBlock, '_nameInLayout', 'test');
        $this->assertEquals(
            'testtest1',
            $this->plugin->afterGetReviewsSummaryHtml($this->productBlock, 'test')
        );
    }

    /**
     * @covers AbstractProduct::getSubscribeHtml
     * @throws \ReflectionException
     */
    public function testGetSubscribeHtml()
    {
        $plugin = $this->createPartialMock(
            AbstractProduct::class,
            ['generateAlertHtml']
        );
        $productModel = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['isSaleable', 'getTypeId', 'getId']
        );
        $registry = $this->createPartialMock(\Magento\Framework\Registry::class, ['unregister', 'register']);
        $subject = $this->createMock(ProductBlock::class);

        $productModel->expects($this->any())->method('isSaleable')->will($this->onConsecutiveCalls(true, false));
        $productModel->expects($this->any())->method('getTypeId')->will($this->onConsecutiveCalls('simple', 'configurable'));
        $productModel->expects($this->any())->method('getId')->willReturn(1000);
        $plugin->expects($this->once())->method('generateAlertHtml')->willReturn('test');

        $this->setProperty($plugin, 'registry', $registry, AbstractProduct::class);
        $plugin->setProduct($productModel);

        $this->assertEquals('', $plugin->getSubscribeHtml($subject));
        $this->assertEquals(
            'test<div class="amxnotif-category-container" data-amsubscribe="1000"></div>',
            $plugin->getSubscribeHtml($subject)
        );
    }

    /**
     * @covers AbstractProduct::getSubscribeBlock
     * @throws \ReflectionException
     */
    public function testGetSubscribeBlock()
    {
        $layout = $this->createMock(LayoutInterface::class);
        $layout->expects($this->any())->method('getBlock')->will($this->onConsecutiveCalls(true, false));
        $layout->expects($this->any())->method('createBlock')->willReturn('true');
        $this->assertTrue($this->invokeMethod($this->plugin, 'getSubscribeBlock', [$layout]));
        $this->assertEquals('true', $this->invokeMethod($this->plugin, 'getSubscribeBlock', [$layout]));
    }

    /**
     * @covers AbstractProduct::prepareSubscribeBlock
     * @throws \ReflectionException
     */
    public function testPrepareSubscribeBlock()
    {
        $block = $this->getObjectManager()->getObject(\Magento\Framework\View\Element\Template::class, []);
        $xnotifHelper = $this->createMock(\Amasty\Xnotif\Helper\Data::class);

        $xnotifHelper->expects($this->any())->method('isLoggedIn')->will($this->onConsecutiveCalls(false, true));
        $xnotifHelper->expects($this->any())->method('getSignupUrl')->willReturn('test');
        $this->setProperty($this->plugin, 'xnotifHelper', $xnotifHelper, AbstractProduct::class);
        $this->setProperty($this->plugin, 'notLoggedTemplate', 'notlogged', AbstractProduct::class);
        $this->setProperty($this->plugin, 'loggedTemplate', 'logged', AbstractProduct::class);

        $this->invokeMethod($this->plugin, 'prepareSubscribeBlock', [$block, $this->productModel, true]);
        $this->assertEquals('notlogged', $block->getTemplate());
        $this->assertEquals($this->productModel, $block->getOriginalProduct());

        $this->invokeMethod($this->plugin, 'prepareSubscribeBlock', [$block, $this->productModel, true]);
        $this->assertEquals('logged', $block->getTemplate());
        $this->assertEquals('alert stock link-stock-alert', $block->getHtmlClass());
        $this->assertEquals('test', $block->getSignupUrl());
    }
}
