<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Xnotif\Test\Unit\Plugins\Bundle;

use Amasty\Xnotif\Plugins\Bundle\Option;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\Xnotif\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Xnotif\Test\Unit\Traits\ReflectionTrait;

/**
 * Class OptionTest
 *
 * @see Option
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectManagerTrait;

    /**
     * @covers Option::addStatusToTitle
     * @throws \ReflectionException
     */
    public function testAddStatusToTitle()
    {
        $plugin = $this->getObjectManager()->getObject(Option::class);
        $object = $this->getObjectManager()->getObject(\Magento\Framework\DataObject::class);
        $this->assertEquals('test', $this->invokeMethod($plugin, 'addStatusToTitle', ['test', $object]));
        $object->setData('amasty_is_salable', true);
        $this->assertEquals(
            'test &nbsp; <span class="amxnotif-bundle-status">(Out of Stock)</span>',
            $this->invokeMethod($plugin, 'addStatusToTitle', ['test', $object])
        );
        $this->assertEquals(
            'test</span> &nbsp; <span class="amxnotif-bundle-status">(Out of Stock)</span>',
            $this->invokeMethod($plugin, 'addStatusToTitle', ['test</span>', $object])
        );
    }
}
