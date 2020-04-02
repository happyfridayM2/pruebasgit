<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


use Amasty\ShopbyBase\Helper\Meta;
use Amasty\ShopbyBase\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class Meta
 *
 * @see Meta
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class MetaTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const PAGE_CONFIG_DESCRIPTION = 'page_description_test';

    const REGISTRY_DESCRIPTION = 'registry_description_test';

    const CATEGORY_DESCRIPTION = 'category_description_test';

    const META_TITLE_KEY = 'meta_title';

    const META_DESCRIPTION_KEY = 'meta_description';

    /**
     * @var Meta|MockObject
     */
    private $metaHelper;

    /**
     * @var \Magento\Framework\Registry|MockObject
     */
    private $registry;

    /**
     * @var \Magento\Framework\View\Page\Config|MockObject
     */
    private $pageConfig;

    public function setUp()
    {
        $this->metaHelper = $this->getMockBuilder(Meta::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->pageConfig = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);

        $this->setProperty($this->metaHelper, 'pageConfig', $this->pageConfig, Meta::class);
        $this->setProperty($this->metaHelper, 'registry', $this->registry, Meta::class);
    }

    /**
     * @covers Meta::getOriginPageMetaTitle
     * @dataProvider getOriginPageMetaTitleDataProvider
     */
    public function testGetOriginPageMetaTitle($metaValue, $regVal, $expected)
    {
        $this->registry->expects($this->any())->method('registry')
            ->willReturn($regVal);
        $category = $this->getObjectManager()->getObject(
            \Magento\Catalog\Model\Category::class
        );
        $category->setData(self::META_TITLE_KEY, $metaValue);

        $result = $this->metaHelper->getOriginPageMetaTitle($category);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Meta::getOriginPageMetaDescription
     * @dataProvider getOriginPageMetaDescriptionDataProvider
     */
    public function testGetOriginPageMetaDescription($metaValue, $confVal, $expected)
    {
        $this->pageConfig->expects($this->any())->method('getDescription')
            ->willReturn($confVal);
        $category = $this->getObjectManager()->getObject(
            \Magento\Catalog\Model\Category::class
        );
        $category->setData(self::META_DESCRIPTION_KEY, $metaValue);

        $result = $this->metaHelper->getOriginPageMetaDescription($category);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getOriginPageMetaTitleDataProvider()
    {
        return [
            [self::CATEGORY_DESCRIPTION, self::REGISTRY_DESCRIPTION, self::CATEGORY_DESCRIPTION],
            [null, self::REGISTRY_DESCRIPTION, self::REGISTRY_DESCRIPTION],
            [null, null, null]
        ];
    }

    public function getOriginPageMetaDescriptionDataProvider()
    {
        return [
            [self::CATEGORY_DESCRIPTION, self::PAGE_CONFIG_DESCRIPTION, self::CATEGORY_DESCRIPTION],
            [null, self::PAGE_CONFIG_DESCRIPTION, self::PAGE_CONFIG_DESCRIPTION],
            [null, null, null]
        ];
    }
}
