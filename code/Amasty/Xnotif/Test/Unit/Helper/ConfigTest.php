<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Xnotif\Test\Unit\Helper;

use Amasty\Xnotif\Test\Unit\Traits\ReflectionTrait;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\Xnotif\Helper\Config;

/**
 * Class ConfigTest
 *
 * @see Config
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;

    /**
     * @covers Config::allowForCurrentCustomerGroup
     * @throws \ReflectionException
     */
    public function testAllowForCurrentCustomerGroup()
    {
        /** @var Config|MockObject $model */
        $model =
            $this->createPartialMock(Config::class, ['getAllowedStockCustomerGroups', 'getAllowedPriceCustomerGroups']);
        $sessionFactory = $this->createPartialMock(\Magento\Customer\Model\SessionFactory::class, ['create']);
        $session = $this->createMock(\Magento\Customer\Model\Session::class);
        $session->expects($this->exactly(2))->method('getCustomerGroupId')->willReturn(1);
        $sessionFactory->expects($this->exactly(2))->method('create')->willReturn($session);

        $this->setProperty($model, 'sessionFactory', $sessionFactory, Config::class);

        $model->expects($this->once())->method('getAllowedStockCustomerGroups')->willReturn([]);

        $this->assertTrue(is_bool($model->allowForCurrentCustomerGroup('stock')));

        $model->expects($this->once())->method('getAllowedPriceCustomerGroups')->willReturn([]);

        $this->assertTrue(is_bool($model->allowForCurrentCustomerGroup('not_stock')));
    }
}
