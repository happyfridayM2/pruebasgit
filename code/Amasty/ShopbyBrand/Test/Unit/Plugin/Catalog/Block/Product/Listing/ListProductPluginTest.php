<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Test\Unit\Plugin\Catalog\Block\Product\Listing;

use Amasty\ShopbyBrand\Test\Unit\Traits;
use Amasty\ShopbyBrand\Plugin\Catalog\Block\Product\Listing\ListProductPlugin;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\ShopbyBrand\Plugin\Catalog\Block\Product\View\BlockHtmlTitlePlugin;

/**
 * Class ListProductPluginTest
 *
 * @see ListProductPlugin
 */
class ListProductPluginTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var ListProductPlugin|MockObject
     */
    private $plugin;

    protected function setUp()
    {
        $this->plugin = $this->createPartialMock(ListProductPlugin::class, [
            'getLogoHtml',
            'isShowOnListing',
            'updateConfigurationData',
            'stopEmulateProduct',
            'startEmulateProduct'
        ]);
    }
    /**
     * @covers ListProductPlugin::getLogoHtml
     */
    public function testGetLogoHtml()
    {
        $this->plugin->expects($this->exactly(2))
            ->method('isShowOnListing')
            ->willReturnOnConsecutiveCalls(false, true);
        $blockHtmlTitlePlugin = $this->getMockBuilder(BlockHtmlTitlePlugin::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateLogoHtml'])
            ->getMock();

        $blockHtmlTitlePlugin->expects($this->any())->method('generateLogoHtml')->willReturn('test');
        $this->setProperty($this->plugin, 'blockHtmlTitlePlugin', $blockHtmlTitlePlugin, ListProductPlugin::class);
        $this->plugin->expects($this->once())->method('updateConfigurationData')->willReturn(true);
        $this->plugin->expects($this->once())->method('stopEmulateProduct')->willReturn(true);
        $this->plugin->expects($this->once())->method('startEmulateProduct')->willReturn(true);

        $actualResult = $this->invokeMethod($this->plugin, 'getLogoHtml', []);
        $this->assertEquals($actualResult, '');

        $actualResult = $this->invokeMethod($this->plugin, 'getLogoHtml', []);
        $this->assertEquals($actualResult, 'test');
    }
}
