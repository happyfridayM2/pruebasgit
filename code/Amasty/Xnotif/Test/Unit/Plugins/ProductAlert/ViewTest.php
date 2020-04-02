<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Xnotif\Test\Unit\Plugins\ProductAlert;

use Amasty\Xnotif\Plugins\ProductAlert\View;
use Magento\Catalog\Api\Data\ProductInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\Xnotif\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Xnotif\Test\Unit\Traits\ReflectionTrait;

/**
 * Class ViewTest
 *
 * @see View
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectManagerTrait;

    /**
     * @covers View::afterToHtml
     * @throws \ReflectionException
     */
    public function testAfterToHtml()
    {
        $config = $this->createMock(\Amasty\Xnotif\Helper\Config::class);
        $registry = $this->createMock(\Magento\Framework\Registry::class);
        $product = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['isSaleable'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $helper = $this->createMock(\Amasty\Xnotif\Helper\Data::class);
        $plugin = $this->getObjectManager()->getObject(
            View::class,
            [
                'config' => $config,
                'helper' => $helper,
                'registry' => $registry
            ]
        );
        $subject = $this->getObjectManager()->getObject(\Magento\ProductAlert\Block\Product\View::class);

        $config->expects($this->any())->method('allowForCurrentCustomerGroup')->will($this->onConsecutiveCalls(false, true, false, true));
        $registry->expects($this->any())->method('registry')->willReturn($product);
        $product->expects($this->any())->method('isSaleable')->willReturn(false);
        $helper->expects($this->any())->method('observeStockAlertBlock')->willReturn('notsalable');
        $helper->expects($this->any())->method('observePriceAlertBlock')->willReturn('price');

        $this->assertEquals('', $plugin->afterToHtml($subject, ''));
        $this->assertEquals('test', $plugin->afterToHtml($subject, 'test'));

        $subject->setNameInLayout('productalert.stock');
        $this->assertEquals('', $plugin->afterToHtml($subject, 'test'));
        $this->assertEquals('notsalable', $plugin->afterToHtml($subject, 'test'));

        $subject->setNameInLayout('productalert.price');
        $this->assertEquals('', $plugin->afterToHtml($subject, 'test'));
        $this->assertEquals('price', $plugin->afterToHtml($subject, 'test'));
    }
}
