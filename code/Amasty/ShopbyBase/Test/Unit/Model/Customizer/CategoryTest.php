<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Test\Unit\Model\Customizer;

use Amasty\ShopbyBase\Model\Customizer\Category;
use Magento\Catalog\Model\Category as CatalogCategory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\ShopbyBase\Test\Unit\Traits;

/**
 * Class CategoryTest
 *
 * @see Category
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Category
     */
    private $model;

    protected function setUp()
    {
        /** @var \Amasty\ShopbySeo\Model\Customizer\Category\Seo|MockObject $seoModifier */
        $seoModifier = $this->createPartialMock(\Amasty\ShopbySeo\Model\Customizer\Category\Seo::class, ['prepareData']);
        $seoModifier->expects($this->any())->method('prepareData')->willReturnCallback(
            function ($category) {
                $category->setData('seo', true);
            }
        );
        /** @var \Magento\Framework\ObjectManager\ObjectManager|MockObject $objectManager */
        $objectManager = $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['get']);
        $objectManager->expects($this->any())->method('get')->willReturnCallback(
            function ($className) use ($seoModifier) {
                if ($className == \Amasty\ShopbySeo\Model\Customizer\Category\Seo::class) {
                    return $seoModifier;
                }

                return null;
            }
        );

        $this->model = $this->getObjectManager()->getObject(Category::class, [
            'objectManager' => $objectManager,
            'customizers' => ['seo' => \Amasty\ShopbySeo\Model\Customizer\Category\Seo::class]
        ]);
    }

    /**
     * @covers Category::_modifyData
     *
     * @throws \ReflectionException
     */
    public function testModifyData()
    {
        $category = $this->getObjectManager()->getObject(CatalogCategory::class);
        $this->invokeMethod($this->model, '_modifyData', ['seo', $category]);

        $this->assertTrue($category->getData('seo'));

        $category = $this->getObjectManager()->getObject(CatalogCategory::class);
        $this->invokeMethod($this->model, '_modifyData', ['brand', $category]);

        $this->assertEmpty($category->getData());
    }
}
