<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


namespace Amasty\Base\Test\Unit\Model;

use Amasty\Base\Model\FeedContent;
use Amasty\Base\Test\Unit\Traits;

/**
 * Class FeedContentTest
 *
 * @see FeedContent
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class FeedContentTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers Feed::getCurrentScheme
     */
    public function testGetCurrentScheme()
    {
        $model = $this->getObjectManager()->getObject(FeedContent::class);

        $baseUrlObject = $this->createMock(\Zend\Uri\Uri::class);
        $baseUrlObject->expects($this->any())->method('getScheme')->willReturnOnConsecutiveCalls('', 'test');

        $this->setProperty($model, 'baseUrlObject', $baseUrlObject);
        $this->assertEquals('', $this->invokeMethod($model, 'getCurrentScheme'));
        $this->assertEquals('test://', $this->invokeMethod($model, 'getCurrentScheme'));
    }
}
