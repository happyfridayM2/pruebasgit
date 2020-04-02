<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Test\Unit\Block\Catalog\Product\ProductList;

use Amasty\ShopbyBrand\Block\Catalog\Product\ProductList\MoreFrom;
use Amasty\ShopbyBrand\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\ShopbyBrand\Test\Unit\Traits\ReflectionTrait;

/**
 * Class MoreFromTest
 *
 * @see MoreFrom
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class MoreFromTest extends \PHPUnit\Framework\TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var MoreFrom
     */
    private $moreFrom;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function setUp()
    {
        $this->product = $this->getObjectManager()->getObject(\Magento\Catalog\Model\Product::class);
        $this->product->setData('color', 'white');

        $this->coreRegistry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();
        $this->helper = $this->createMock(\Amasty\ShopbyBrand\Helper\Data::class);
        $this->moreFrom = $this->getObjectManager()->getObject(
            MoreFrom::class,
            [
                '_coreRegistry' => $this->coreRegistry,
                'helper' => $this->helper,
            ]
        );
    }

    /**
     * @covers MoreFrom::getTitle
     *
     * @dataProvider getTestDatabase
     *
     * @throws \ReflectionException
     */
    public function testGetTitle($title, $expectedResult = 'More from this Brand') {

        $this->coreRegistry->expects($this->any())
            ->method('registry')
            ->with('product')
            ->will($this->returnValue($this->product));

        $this->helper->expects($this->any())->method('getModuleConfig')->will($this->returnValue($title));
        $this->helper->expects($this->any())->method('getBrandAttributeCode')->will($this->returnValue('color'));

        $resultOrigMethod = $this->moreFrom->getTitle();

        $this->assertEquals($expectedResult, $resultOrigMethod);
    }

    /**
     * @return array
     */
    public function getTestDatabase()
    {
        return [
            ['test', 'test'],
            ['{brand_name}'],
        ];
    }
}
