<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Test\Model\Customizer\Category;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\ShopbySeo\Test\Unit\Traits;
use Amasty\ShopbySeo\Model\Customizer\Category\Seo;

/**
 * Class SeoTest
 *
 * @see Seo
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class SeoTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const ROOT_CATEGORY_ID = 2;
    const BASE_URL = 'http://some-base-url/';
    const CURRENT_URL = 'http://some-test/test.html?some-param=1';
    const ROOT_URL = 'http://some-test/all-products';
    const WITHOUT_GET = 'http://some-test/test.html';

    /**
     * @var Seo
     */
    private $model;

    /**
     * @var MockObject|\Amasty\ShopbySeo\Helper\Data
     */
    protected $helper;

    /**
     * @var MockObject|\Amasty\Shopby\Model\Request
     */
    protected $amshopbyRequest;

    /**
     * @var MockObject|\Magento\Catalog\Model\Category
     */
    protected $rootCategory;

    /**
     * @var MockObject|\Amasty\ShopbyBase\Model\Category\Manager
     */
    protected $categoryManager;

    /**
     * @var MockObject|\Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    protected function setUp()
    {
        $this->urlBuilder = $this->createMock(\Amasty\ShopbyBase\Model\UrlBuilder::class);
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->helper = $this->createMock(\Amasty\ShopbySeo\Helper\Data::class);
        $this->amshopbyRequest = $this->createMock(\Amasty\Shopby\Model\Request::class);
        $block = $this->createMock(\Magento\LayeredNavigation\Block\Navigation::class);
        $this->categoryManager = $this->createMock(\Amasty\ShopbyBase\Model\Category\Manager::class);

        $this->rootCategory = $this->getMockBuilder(
            \Magento\Catalog\Model\Category::class)
            ->setMethods(['getId', 'getUrl'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->categoryManager->method('getRootCategoryId')->willReturn(self::ROOT_CATEGORY_ID);
        $this->categoryManager->method('getBaseUrl')->willReturn(self::BASE_URL);
        $this->rootCategory->method('getId')->willReturn(self::ROOT_CATEGORY_ID);
        $this->urlBuilder->expects($this->any())->method('getCurrentUrl')->willReturn(self::CURRENT_URL);
        $layout->expects($this->any())->method('getAllBlocks')->willReturn([$block]);

        $this->model = $this->getObjectManager()->getObject(
            Seo::class,
            [
                'categoryManager' => $this->categoryManager,
                'helper' => $this->helper,
                'url' => $this->urlBuilder,
                'layout' => $layout,
                'amshopbyRequest' => $this->amshopbyRequest
            ]
        );
    }

    /**
     * @covers Seo::getRootModeCanonical
     */
    public function testRootCanonicalModeDefault()
    {
        $this->amshopbyRequest->expects($this->any())->method('getRequestParams')->willReturn('test');
        $this->assertEquals(self::CURRENT_URL, $this->model->getRootModeCanonical());
    }

    /**
     * @covers Seo::getRootModeCanonical
     */
    public function testRootCanonicalModeForRootCurrent()
    {
        $this->helper->expects($this->any())->method('getCanonicalRoot')->willReturn('root_current');
        $this->assertEquals(self::CURRENT_URL, $this->model->getRootModeCanonical());
    }

    /**
     * @covers Seo::getRootModeCanonical
     */
    public function testRootCanonicalModeForRootPure()
    {
        $this->urlBuilder->expects($this->any())->method('getUrl')->willReturn(self::ROOT_URL);
        $this->helper->expects($this->any())->method('getCanonicalRoot')->willReturn('root_pure');
        $this->assertEquals(self::ROOT_URL, $this->model->getRootModeCanonical());
    }

    /**
     * @covers Seo::getRootModeCanonical
     */
    public function testRootCanonicalModeForFirstAttr()
    {
        $this->urlBuilder->expects($this->any())->method('getUrl')->willReturn(self::CURRENT_URL);
        $this->helper->expects($this->any())->method('getCanonicalRoot')->willReturn('root_first_attribute');
        $this->assertEquals(self::CURRENT_URL, $this->model->getRootModeCanonical());
    }

    /**
     * @covers Seo::getRootModeCanonical
     */
    public function testRootCanonicalModeWithoutGet()
    {
        $this->helper->expects($this->any())->method('getCanonicalRoot')->willReturn('root_cut_off_get');
        $this->assertEquals(self::WITHOUT_GET, $this->model->getRootModeCanonical());
    }

    /**
     * @covers Seo::getNavigationBlock
     */
    public function testGetNavigationBlock()
    {
        $this->assertNotNull($this->invokeMethod($this->model, 'getNavigationBlock'));
        $this->setProperty($this->model, 'navigationBlock', true);
        $this->assertTrue($this->invokeMethod($this->model, 'getNavigationBlock'));
    }

    /**
     * @covers Seo::getCanonicalMode
     */
    public function testGetCanonicalMode()
    {
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $category->expects($this->any())->method('getId')->will($this->onConsecutiveCalls(1, 2));

        $this->assertEquals('category', $this->model->getCanonicalMode($category));
        $this->assertEquals('root', $this->model->getCanonicalMode($category));
    }

    /**
     * @covers Seo::getCategoryModeCanonical
     */
    public function testGetCategoryModeCanonicalDefault()
    {
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $category->expects($this->any())->method('getUrl')->willReturn(self::CURRENT_URL);
        $this->assertEquals(self::CURRENT_URL, $this->model->getCategoryModeCanonical($category));
    }

    /**
     * @covers Seo::getCategoryModeCanonical
     */
    public function testGetCategoryModeCanonicalCurrent()
    {
        $this->helper->expects($this->any())->method('getCanonicalCategory')->willReturn('category_current');
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $this->assertEquals(self::CURRENT_URL, $this->model->getCategoryModeCanonical($category));
    }

    /**
     * @covers Seo::getCategoryModeCanonical
     */
    public function testGetCategoryModeCanonicalPure()
    {
        $this->helper->expects($this->any())->method('getCanonicalCategory')->willReturn('category_pure');
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $category->expects($this->any())->method('getUrl')->willReturn(self::ROOT_URL);
        $this->assertEquals(self::ROOT_URL, $this->model->getCategoryModeCanonical($category));
    }

    /**
     * @covers Seo::getCategoryModeCanonical
     */
    public function testGetCategoryModeCanonicalBrand()
    {
        $this->helper->expects($this->any())->method('getCanonicalCategory')->willReturn('category_brand_filter');
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $category->expects($this->any())->method('getUrl')->willReturn(self::ROOT_URL);
        $this->assertEquals(self::ROOT_URL, $this->model->getCategoryModeCanonical($category));
    }

    /**
     * @covers Seo::getCategoryModeCanonical
     */
    public function testGetCategoryModeCanonicalFirstAttr()
    {
        $this->helper->expects($this->any())->method('getCanonicalCategory')->willReturn('category_first_attribute');
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $category->expects($this->any())->method('getUrl')->willReturn(self::CURRENT_URL);
        $this->assertEquals(self::CURRENT_URL, $this->model->getCategoryModeCanonical($category));
    }

    /**
     * @covers Seo::getCategoryModeCanonical
     */
    public function testGetCategoryModeCanonicalWithoutGet()
    {
        $this->helper->expects($this->any())->method('getCanonicalCategory')->willReturn('category_cut_off_get');
        $category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $category->expects($this->any())->method('getUrl')->willReturn(self::CURRENT_URL);
        $this->assertEquals(self::WITHOUT_GET, $this->model->getCategoryModeCanonical($category));
    }
}
