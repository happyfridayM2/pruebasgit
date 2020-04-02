<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Xnotif\Test\Unit\Plugins\Backend\Model;

use Amasty\Xnotif\Plugins\Backend\Model\Url;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\Xnotif\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Xnotif\Test\Unit\Traits\ReflectionTrait;

/**
 * Class UrlTest
 *
 * @see Url
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UrlTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectManagerTrait;

    /**
     * @var Url
     */
    private $plugin;

    protected function setUp()
    {
        $registry = $this->createMock(\Magento\Framework\Registry::class);
        $registry->expects($this->any())->method('registry')->with('xnotif_test_notification')
            ->will($this->onConsecutiveCalls(true, false));
        $this->plugin = $this->getObjectManager()->getObject(Url::class, ['registry' => $registry]);
    }

    /**
     * @covers Url::afterGetAreaFrontName
     * @throws \ReflectionException
     */
    public function testAfterGetAreaFrontName()
    {
        $subject = $this->createMock(\Magento\Backend\Model\Url::class);
        $this->assertEquals('', $this->plugin->afterGetAreaFrontName($subject, 'test'));
        $this->assertEquals('test', $this->plugin->afterGetAreaFrontName($subject, 'test'));
    }
}
