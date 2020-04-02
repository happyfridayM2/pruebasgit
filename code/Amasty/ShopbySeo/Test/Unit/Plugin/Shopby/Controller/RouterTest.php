<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


use Amasty\ShopbySeo\Plugin\Shopby\Controller\Router;
use Amasty\ShopbySeo\Test\Unit\Traits;

/**
 * Class Router
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

    /**
     * @covers Router::afterCheckMatchExpressions
     * @dataProvider afterCheckMatchExpressionsDataProvider
     */
    public function testAfterCheckMatchExpressions($seoUrlEnabled, $result, $expected)
    {
        $subject = $this->createMock(\Amasty\Shopby\Controller\Router::class);

        $request = $this->getObjectManager()
            ->getObject(\Magento\Framework\App\Request\Http::class);
        $urlHelper = $this->createMock(\Amasty\ShopbySeo\Helper\Url::class);
        $urlHelper->expects($this->any())->method('isSeoUrlEnabled')
            ->willReturn($seoUrlEnabled);

        $router = $this->createPartialMock(Router::class, []);
        $this->setProperty($router, 'urlHelper', $urlHelper, Router::class);
        $this->setProperty($router, 'request', $request, Router::class);

        $this->assertEquals($expected, $router->afterCheckMatchExpressions($subject, $result));
    }

    public function afterCheckMatchExpressionsDataProvider()
    {
        return [
            [false, false, false],
            [true, true, true],
            [true, false, false]
        ];
    }
}
