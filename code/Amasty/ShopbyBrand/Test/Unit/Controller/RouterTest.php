<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Test\Unit\Controller;

use Amasty\ShopbyBrand\Controller\Router;
use Amasty\ShopbyBrand\Test\Unit\Traits;

/**
 * Class RouterTest
 *
 * @see Router
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class RouterTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const BRAND_PATH_IDENTIFIER = '/test';

    const BRAND_URL_KEY = 'test';

    const BRAND_ATR_CODE = 'batr';

    const BRAND_ALIASES = [
        '1' => 'test'
    ];

    /**
     * @var Router
     */
    private $router;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data $brandHelper
     */
    private $brandHelper;

    /**
     * @var \Magento\Framework\App\ActionFactory $actionFactory
     */
    private $actionFactory;

    public function setUp()
    {
        $this->brandHelper = $this->createMock(\Amasty\ShopbyBrand\Helper\Data::class);
        $this->brandHelper->expects($this->any())->method('getBrandUrlKey')
            ->will($this->returnValue(self::BRAND_URL_KEY));
        $this->brandHelper->expects($this->any())->method('getBrandAttributeCode')
            ->will($this->returnValue(self::BRAND_ATR_CODE));
        $this->brandHelper->expects($this->any())->method('getBrandAliases')
            ->will($this->returnValue(self::BRAND_ALIASES));

        $permissionHelper = $this->createMock(\Amasty\ShopbyBase\Helper\PermissionHelper::class);
        $permissionHelper->expects($this->any())->method('checkPermissions')
            ->will($this->returnValue(true));

        $this->actionFactory = $this->createMock(\Magento\Framework\App\ActionFactory::class);

        $this->router = $this->getObjectManager()->getObject(
            Router::class,
            [
                'brandCode' => self::BRAND_ATR_CODE,
                'permissionHelper' => $permissionHelper,
                'actionFactory' => $this->actionFactory
            ]
        );
        $this->setProperty($this->router, 'brandHelper', $this->brandHelper);
    }

    /**
     * @covers Router::match
     */
    public function testMatch()
    {
        $request = $this->getObjectManager()->getObject(
            \Magento\Framework\App\Request\Http::class,
            [
                'pathInfo' => self::BRAND_PATH_IDENTIFIER
            ]
        );
        $this->actionFactory->expects($this->any())->method('create')
        ->will($this->returnValue($this->createMock(\Magento\Framework\App\Action\Forward::class)));
        $result = $this->invokeMethod($this->router, 'match', [$request]);

        $this->assertInstanceOf(\Magento\Framework\App\Action\Forward::class, $result);

        $request->setPathInfo(self::BRAND_PATH_IDENTIFIER . '2');
        $result = $this->invokeMethod($this->router, 'match', [$request]);

        $this->assertEquals(null, $result);
    }

    /**
     * @covers Router::matchBrandParams
     *
     * @dataProvider brandPathProvider
     */
    public function testMatchBrandParams($path, $resArr)
    {
        $result = $this->invokeMethod(
            $this->router,
            'matchBrandParams',
            [$path]
        );
        $this->assertEquals($resArr, $result);
    }

    /**
     * Data provider for testMatchBrandParams
     * @return array
     */
    public function brandPathProvider()
    {
        return [
            ['/test', ['batr' => 1]],
            ['/test2', []]
        ];
    }
}
