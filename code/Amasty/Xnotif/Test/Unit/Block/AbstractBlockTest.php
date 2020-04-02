<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


/**
 * @codingStandardsIgnoreFile
 */
 
 namespace Amasty\Xnotif\Test\Unit\Block;

use Amasty\Xnotif\Block\AbstractBlock;
use Amasty\Xnotif\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AbstractBlock
 *
 * @see AbstractBlock
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractBlockTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const IMAGE_URL = 'http://testsite.com/media/tesurl/test.png';

    const PRODUCT_ID = 1;

    const PARENT_PRODUCT_ID = 2;

    const ATTRIBUTES_TEST = [
        'attr1' => 'attr1',
        'attr2' => 'attr2'
    ];

    /**
     * @var AbstractBlock|MockObject
     */
    private $abstractBlock;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|MockObject
     */
    private $product;

    /**
     * @var \Magento\Catalog\Helper\Image|MockObject
     */
    private $imageHelper;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable|MockObject
     */
    private $configurableModel;

    public function setUp()
    {
        $this->abstractBlock = $this->createPartialMock(
            AbstractBlock::class,
            ['isParentImageEnabled', 'getProduct', 'isProductSalable']
        );
        $this->product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getImage', 'getTypeInstance']
        );
        $this->product->expects($this->any())->method('getImage')
            ->willReturn('test.png');

        $this->imageHelper = $this->createPartialMock(
            \Magento\Catalog\Helper\Image::class,
            ['init', 'getUrl']
        );

        $this->configurableModel = $this->createMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class
        );

        $this->imageHelper->expects($this->any())->method('getUrl')->willReturn(self::IMAGE_URL);
        $this->imageHelper->expects($this->any())->method('init')->willReturn($this->imageHelper);

        $this->setProperty(
            $this->abstractBlock,
            'imageHelper',
            $this->imageHelper,
            AbstractBlock::class
        );
        $this->setProperty(
            $this->abstractBlock,
            'configurableModel',
            $this->configurableModel,
            AbstractBlock::class
        );
    }

    /**
     * @covers AbstractBlock::getImageSrc
     * @dataProvider getImageSrcDataProvider
     */
    public function testGetImageSrc($isParentImage, $expected)
    {
        $this->abstractBlock->expects($this->any())->method('isParentImageEnabled')
            ->willReturn($isParentImage);
        $this->abstractBlock->expects($this->any())->method('getProduct')
            ->with(self::PARENT_PRODUCT_ID)
            ->willReturn($this->product);
        $this->product->setId(self::PRODUCT_ID);

        $this->configurableModel->expects($this->any())->method('getParentIdsByChild')
            ->with(self::PRODUCT_ID)
            ->willReturn([self::PARENT_PRODUCT_ID]);

        $result = $this->abstractBlock->getImageSrc($this->product);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers AbstractBlock::getParentProductId
     * @dataProvider getParentProductIdDataProvider
     */
    public function testGetParentProductId($childId, $parentId, $expected)
    {
        $this->configurableModel->expects($this->once())->method('getParentIdsByChild')
            ->with($childId)
            ->willReturn($parentId);

        $result = $this->invokeMethod($this->abstractBlock, 'getParentProductId', [$childId]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers AbstractBlock::getStockStatus
     * @dataProvider getStockStatusDataProvider
     */
    public function testGetStockStatus($isSalable, $expects)
    {
        $this->abstractBlock->expects($this->once())->method('isProductSalable')
            ->willReturn($isSalable);

        $result = $this->abstractBlock->getStockStatus($this->product);
        $this->assertEquals($result, $expects);
    }

    /**
     * @covers AbstractBlock::getSupperAttributesByChildId
     * @dataProvider getSupperAttributesByChildIdDataProvider
     */
    public function testGetSupperAttributesByChildId($childId, $parentId, $expected)
    {
        $typeInstance = $this->createPartialMock(
                \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class,
                ['getConfigurableAttributes']
            );
        $typeInstance->expects($this->any())->method('getConfigurableAttributes')
            ->with($this->product)
            ->willReturn(self::ATTRIBUTES_TEST);

        $this->configurableModel->expects($this->once())->method('getParentIdsByChild')
            ->with($childId)
            ->willReturn($parentId);

        $this->product->expects($this->any())->method('getTypeInstance')->willReturn($typeInstance);

        $this->abstractBlock->expects($this->any())->method('getProduct')
            ->with(self::PARENT_PRODUCT_ID)
            ->willReturn($this->product);

        $result = $this->abstractBlock->getSupperAttributesByChildId($childId);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data Provider for getImageSrc test
     * @return array
     */
    public function getImageSrcDataProvider()
    {
        return [
            [false, self::IMAGE_URL],
            [true, self::IMAGE_URL]
        ];
    }

    /**
     * Data Provider for getParentProductId test
     * @return array
     */
    public function getParentProductIdDataProvider()
    {
        return [
            [self::PRODUCT_ID, [self::PARENT_PRODUCT_ID], self::PARENT_PRODUCT_ID],
            [self::PARENT_PRODUCT_ID, null,null]
        ];
    }

    /**
     * Data Provider for getStockStatus test
     * @return array
     */
    public function getStockStatusDataProvider()
    {
        return [
            [true, 'In Stock'],
            [false, 'Out of Stock']
        ];
    }

    /**
     * Data Provider for getSupperAttributesByChildId test
     * @return array
     */
    public function getSupperAttributesByChildIdDataProvider()
    {
        return [
            [self::PRODUCT_ID, [self::PARENT_PRODUCT_ID], self::ATTRIBUTES_TEST],
            [self::PARENT_PRODUCT_ID, null, []]
        ];
    }
}
