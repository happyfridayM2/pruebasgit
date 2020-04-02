<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Xnotif\Test\Unit\Block\Adminhtml\Stock\Renderer;

use Amasty\Xnotif\Block\Adminhtml\Stock\Renderer\Website;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\Xnotif\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Xnotif\Test\Unit\Traits\ReflectionTrait;

/**
 * Class WebsiteTest
 *
 * @see Website
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WebsiteTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectManagerTrait;

    /**
     * @covers Website::convertIdsToLabels
     * @throws \ReflectionException
     */
    public function testConvertIdsToLabels()
    {
        $block = $this->createPartialMock(Website::class, ['getWebsiteOptions']);
        $block->expects($this->any())->method('getWebsiteOptions')
            ->will($this->onConsecutiveCalls([], [['value' => 'test', 'label' => 2]]));
        $this->assertEquals('', $this->invokeMethod($block, 'convertIdsToLabels', ['test']));
        $this->assertEquals(2, $this->invokeMethod($block, 'convertIdsToLabels', ['test']));
    }
}
