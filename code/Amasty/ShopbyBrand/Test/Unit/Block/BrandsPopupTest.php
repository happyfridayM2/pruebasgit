<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Test\Unit\Block;

use Amasty\ShopbyBrand\Block\BrandsPopup;
use Amasty\ShopbyBrand\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class BrandsPopupTest
 *
 * @see BrandsPopup
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class BrandsPopupTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var BrandsPopup
     */
    private $block;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $brandHelper;

    public function setUp()
    {
        $this->request = $this->getObjectManager()->getObject(\Magento\Framework\App\Request\Http::class);
        $this->brandHelper = $this->createMock(\Amasty\ShopbyBrand\Helper\Data::class);
        $this->block = $this->getObjectManager()->getObject(
            BrandsPopup::class,
            [
            'brandHelper' => $this->brandHelper
            ]
        );
        $this->setProperty($this->block, '_request', $this->request);
        $this->setProperty(
            $this->request,
            'originalPathInfo',
            '/',
            \Magento\Framework\App\Request\Http::class
        );
        $this->brandHelper->expects($this->any())->method('getAllBrandsUrl')->willReturn('test');
    }

    /**
     * @covers BrandsPopup::isAllBrandsPage
     */
    public function testIsAllBrandsPage()
    {
        $this->assertFalse($this->block->isAllBrandsPage());
        $this->setProperty(
            $this->request,
            'originalPathInfo',
            'test',
            \Magento\Framework\App\Request\Http::class
        );
        $this->assertTrue($this->block->isAllBrandsPage());
        $this->setProperty(
            $this->request,
            'originalPathInfo',
            'badtest',
            \Magento\Framework\App\Request\Http::class
        );
        $this->assertFalse($this->block->isAllBrandsPage());
    }
}
