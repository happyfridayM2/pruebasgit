<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Test\Unit\Controller;

use Amasty\ShopbySeo\Controller\Router;
use Amasty\ShopbySeo\Helper\UrlParser;
use Amasty\ShopbySeo\Test\Unit\Traits;
use Magento\Framework\App\RequestInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

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

    /**
     * @var Router
     */
    private $controller;

    /**
     * @var Router
     */
    private $helper;

    public function setUp()
    {
        $urlParser = $this->createMock(UrlParser::class);
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->helper = $this->createMock(\Amasty\ShopbySeo\Helper\Data::class);

        $urlParser->expects($this->any())->method('checkSeoParams')->willReturn(true);
        $scopeConfig->expects($this->any())->method('getValue')->willReturn('.html');

        $this->controller = $this->getObjectManager()->getObject(
            Router::class,
            [
                'urlParser' => $urlParser,
                'scopeConfig' => $scopeConfig,
                'helper' => $this->helper
            ]
        );
    }

    /**
     * @covers Router::checkSeoParams
     */
    public function testCheckSeoParamsWithoutAjax()
    {
        $request = $request = $this->getRequestMock();
        $request->expects($this->once())->method('setMetaData')->willReturn('test');
        $request->expects($this->once())->method('isAjax')->willReturn(false);

        $this->invokeMethod($this->controller, 'checkSeoParams', [$request, []]);
    }

    /**
     * @covers Router::checkSeoParams
     */
    public function testCheckSeoParamsWithAjax()
    {
        $request = $this->getRequestMock();
        $request->expects($this->never())->method('setMetaData')->willReturn('test');
        $request->expects($this->once())->method('isAjax')->willReturn(true);

        $this->invokeMethod($this->controller, 'checkSeoParams', [$request, []]);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getRequestMock()
    {
        $request = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['setMetaData', 'getUserParams', 'getQuery', 'isAjax'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $request->expects($this->once())->method('getUserParams')->willReturn(['test']);
        $request->expects($this->once())->method('getQuery')->willReturn(['test']);

        return $request;
    }

    /**
     * @covers Router::modifyRequest
     */
    public function testModifyRequest()
    {
        $request = $this->getObjectManager()->getObject(\Magento\Framework\App\Request\Http::class);

        $this->controller->modifyRequest($request, '', ['a']);
        $this->assertEquals(['shopby_seo_has_parsed_params_flag' => true], $request->getMetaData());
        $this->assertEquals(['a'], $request->getParams());

        $this->setProperty($this->controller, 'isSuffixRemoved', true);
        $this->controller->modifyRequest($request, 'key', []);
        $this->assertEquals('key.html', $request->getPathInfo());
    }

    /**
     * @covers Router::match
     */
    public function testMatchWithSkipMatch()
    {
        $this->helper->expects($this->once())->method('isAllowedRequest')->willReturn(false);
        $request = $this->getObjectManager()->getObject(\Magento\Framework\App\Request\Http::class);

        $this->assertFalse($this->controller->match($request));
        $this->assertEquals(['shopby_seo_skip_request_flag' => true], $request->getMetaData());
    }

    /**
     * @covers Router::removeSuffix
     */
    public function testRemoveSuffixWithoutAjax()
    {
        $identifier = '/';
        $request = $this->getObjectManager()->getObject(\Magento\Framework\App\Request\Http::class);
        $this->invokeMethod($this->controller, 'removeSuffix', [&$identifier, $request]);
        $this->assertEquals('/', $identifier);

        $identifier = '/category.html';
        $this->invokeMethod($this->controller, 'removeSuffix', [&$identifier, $request]);
        $this->assertEquals(['shopby_seo_missed_suffix_redirect_flag' => true], $request->getmetaData());

        $identifier = '/category/';
        $this->invokeMethod($this->controller, 'removeSuffix', [&$identifier, $request]);
        $this->assertEquals(['shopby_seo_missed_suffix_redirect_flag' => true], $request->getmetaData());
    }

    /**
     * @covers Router::removeSuffix
     */
    public function testRemoveSuffixWithAjax()
    {
        $identifier = '/';
        $request = $this->getObjectManager()->getObject(\Magento\Framework\App\Request\Http::class);
        $request->setParam('ajax', 1);
        $this->invokeMethod($this->controller, 'removeSuffix', [&$identifier, $request]);
        $this->assertEquals('/', $identifier);

        $identifier = '/category.html';
        $this->invokeMethod($this->controller, 'removeSuffix', [&$identifier, $request]);
        $this->assertEquals([], $request->getmetaData());

        $identifier = '/category/';
        $this->invokeMethod($this->controller, 'removeSuffix', [&$identifier, $request]);
        $this->assertEquals([], $request->getmetaData());
    }

    /**
     * @covers Router::getSeoPartAndIdentifier
     */
    public function testGetSeoPartAndIdentifier()
    {
        $request = $this->getObjectManager()->getObject(\Magento\Framework\App\Request\Http::class);
        $this->helper->expects($this->any())->method('getFilterWord')->will($this->onConsecutiveCalls(false, 'test', 'test'));
        $result = $this->invokeMethod($this->controller, 'getSeoPartAndIdentifier', ['test/category', $request]);
        $this->assertEquals(['category', 'test'], $result);

        $result = $this->invokeMethod($this->controller, 'getSeoPartAndIdentifier', ['test/category', $request]);
        $this->assertEquals(['shopby_seo_skip_request_flag'=> true, 'shopby_seo_redirect_flag' => true], $request->getMetaData());

        $result = $this->invokeMethod($this->controller, 'getSeoPartAndIdentifier', ['/test/category', $request]);
        $this->assertEquals(['category', ''], $result);
    }
}
