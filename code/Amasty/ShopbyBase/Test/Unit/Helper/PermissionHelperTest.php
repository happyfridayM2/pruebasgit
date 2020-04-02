<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


use Amasty\ShopbyBase\Helper\PermissionHelper;
use Amasty\ShopbyBase\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class PermissionHelper
 *
 * @see PermissionHelper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class PermissionHelperTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const CUSTOMER_GROUP_ID = 1;

    const GROUPS_STRING = 'test1,test2';

    const GROUPS_ARRAY = [
        0 => 'test1',
        1 => 'test2'
    ];

    /**
     * @var PermissionHelper|MockObject
     */
    private $permissionHelper;

    /**
     * @var \Magento\Framework\Config\Scope|MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session|MockObject
     */
    private $customerSession;

    public function setUp()
    {
        $this->permissionHelper = $this->getMockBuilder(PermissionHelper::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $customer = $this->createMock(\Magento\Customer\Model\Customer::class);
        $customer->expects($this->any())->method('getGroupId')
            ->willReturn(self::CUSTOMER_GROUP_ID);

        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->customerSession->expects($this->any())->method('getCustomer')
            ->willReturn($customer);

        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->setProperty(
            $this->permissionHelper,
            'customerSession',
            $this->customerSession,
            PermissionHelper::class
        );
        $this->setProperty(
            $this->permissionHelper,
            'scopeConfig',
            $this->scopeConfig,
            PermissionHelper::class
        );
    }

    /**
     * @covers PermissionHelper::getCustomerGroupId
     *
     * @dataProvider getCustomerGroupIdDataProvider
     */
    public function testGetCustomerGroupId($isLogged, $expected)
    {
        $this->customerSession->expects($this->any())->method('isLoggedIn')
            ->willReturn($isLogged);

        $result = $this->permissionHelper->getCustomerGroupId();
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers PermissionHelper::getCustomerGroupPermissions
     *
     * @dataProvider getCustomerGroupPermissionsDataProvider
     */
    public function testGetCustomerGroupPermissions($configVal, $expected)
    {
        $this->scopeConfig->expects($this->any())->method('getValue')
            ->willReturn($configVal);
        $result = $this->permissionHelper->getCustomerGroupPermissions();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getCustomerGroupPermissionsDataProvider()
    {
        return [
            [self::GROUPS_STRING, self::GROUPS_ARRAY],
            [null, [0 => '']]
        ];
    }

    /**
     * @return array
     */
    public function getCustomerGroupIdDataProvider()
    {
        return [
            [true, self::CUSTOMER_GROUP_ID],
            [false, 0]
        ];
    }
}
