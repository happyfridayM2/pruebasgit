<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Xnotif\Test\Unit\Block\Adminhtml\Stock;

use Amasty\Xnotif\Block\Adminhtml\Stock\Grid;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\Xnotif\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Xnotif\Test\Unit\Traits\ReflectionTrait;

/**
 * Class GridTest
 *
 * @see Grid
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GridTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectManagerTrait;

    /**
     * @covers Grid::getBackgroundColor
     * @dataProvider getDataForBackgroundColor
     *
     * @param int $value
     * @param string $result
     *
     * @throws \ReflectionException
     */
    public function testGetBackgroundColor($value, $result)
    {
        $block = $this->createPartialMock(Grid::class, []);
        $this->assertEquals($result, $this->invokeMethod($block, 'getBackgroundColor', [$value]));
    }

    public function getDataForBackgroundColor()
    {
        return [
            [
                '0',
                'green'
            ],
            [
                '1',
                'lightcoral'
            ],
            [
                '2',
                'indianred'
            ],
            [
                '3',
                'brown'
            ],
            [
                '4',
                'firebrick'
            ],
            [
                '5',
                'darkred'
            ],
            [
                '63',
                'red'
            ],
        ];
    }
}
