<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Test\Unit\Block\Widget;

use Amasty\ShopbyBrand\Block\Widget\BrandList;
use Amasty\ShopbyBrand\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory;

/**
 * Class BrandListTest
 *
 * @see BrandList
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class BrandListTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var BrandList|MockObject
     */
    private $block;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $brandHelper;

    /**
     * @var array
     */
    private $brandListAfterSort = [
        'A' => ['items' => [['label' => 'a']], 'count' => 1],
        'B' => ['items' => [['label' => 'b']], 'count' => 1],
        'C' => ['items' => [['label' => 'c']], 'count' => 1]
    ];

    /**
     * @var array
     */
    private $resultBrandList = [
        ['A' => [['label' => 'a']]],
        ['B' => [['label' => 'b']]],
        ['C' => [['label' => 'c']]]
    ];

    /**
     * @var array
     */
    private $notSortedList = [
        ['label' => 'a'],
        ['label' => 'c'],
        ['label' => 'b']
    ];

    /**
     * @var array
     */
    private $sortedList = [
        ['label' => 'a'],
        ['label' => 'b'],
        ['label' => 'c']
    ];

    public function setUp()
    {
        $context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $repository = $this->createMock(\Magento\Catalog\Model\Product\Attribute\Repository::class);
        $optionSettingFactory = $this->createMock(\Amasty\ShopbyBase\Model\OptionSettingFactory::class);
        $optionSettingCollectionFactory = $this->createPartialMock(CollectionFactory::class, ['create']);
        $collectionProvider = $this->createMock(\Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider::class);
        $productUrl = $this->createMock(\Magento\Catalog\Model\Product\Url::class);
        $categoryRepository = $this->createMock(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
        $this->dataHelper = $this->createMock(\Amasty\ShopbyBrand\Helper\Data::class);
        $messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $amUrlBuilder = $this->createMock(\Amasty\ShopbyBase\Api\UrlBuilderInterface::class);
        $productCount = $this->createMock(\Amasty\ShopbyBrand\Model\ProductCount::class);
        $this->brandHelper = $this->createMock(\Amasty\ShopbyBrand\Helper\Data::class);

        $this->block = $this->getObjectManager()->getObject(
            BrandList::class,
            [
                'context' => $context,
                'repository' => $repository,
                'optionSettingFactory' => $optionSettingFactory,
                'optionSettingCollectionFactory' => $optionSettingCollectionFactory,
                'collectionProvider' => $collectionProvider,
                'productUrl' => $productUrl,
                'categoryRepository' => $categoryRepository,
                'dataHelper' => $this->dataHelper,
                'messageManager' => $messageManager,
                'amUrlBuilder' => $amUrlBuilder,
                'productCount' => $productCount,
                'brandHelper' => $this->brandHelper,
            ]
        );
    }


    /**
     * @covers BrandList::getIndex
     */
    public function testGetIndexWithoutItems()
    {
        $this->assertEquals([], $this->block->getIndex());
    }

    /**
     * @covers BrandList::getIndex
     */
    public function testGetIndexWithItems()
    {
        $this->setProperty($this->block, 'items', [['label' =>'a'], ['label' =>'b'], ['label' =>'c']]);
        $this->assertEquals($this->resultBrandList, $this->block->getIndex());
    }

    /**
     * @covers BrandList::sortByLetters
     */
    public function testSortByLetters()
    {
        $sortedValues = $this->invokeMethod($this->block, 'sortByLetters', [$this->notSortedList]);
        $this->assertEquals($this->brandListAfterSort, $sortedValues);
    }

    /**
     * @covers BrandList::breakByColumns
     */
    public function testBreakByColumns()
    {
        $resultValue = $this->invokeMethod($this->block, 'breakByColumns', [$this->brandListAfterSort]);
        $this->assertEquals($this->resultBrandList, $resultValue);
    }

    /**
     * @covers BrandList::breakByColumnsWithColumnsNumber
     */
    public function testBreakByColumnsWithColumnsNumber()
    {
        $resultBrandList = [
            ['A' => [['label' => 'a']], 'B' => [['label' => 'b']]],
            ['C' => [['label' => 'c']]]
        ];
        $this->block->setColumns(2);
        $resultValue = $this->invokeMethod($this->block, 'breakByColumns', [$this->brandListAfterSort]);
        $this->assertEquals($resultBrandList, $resultValue);
    }

    /**
     * @covers BrandList::getItemDataWithEmptyResult
     */
    public function testGetItemDataWithEmptyResult()
    {
        $option = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $optionSetting = $this->createMock(\Amasty\ShopbyBase\Api\Data\OptionSettingInterface::class);
        $this->dataHelper->expects($this->any())->method('isDisplayZero')->will($this->returnValue(false));

        $resultValue = $this->invokeMethod($this->block, 'getItemData', [$option, $optionSetting]);
        $this->assertEquals([], $resultValue);
    }

    /**
     * @covers BrandList::getItemDataWithNotEmptyResult
     */
    public function testGetItemDataWithNotEmptyResult()
    {
        $result = [
            'brandId' => 1,
            'label' => 'label',
            'url' => null,
            'img' => 'label',
            'image' => 'label',
            'description' => 'label',
            'short_description' => 'label',
            'cnt' => 0,
            'alt' => 'label'
        ];
        $option = $this->getObjectManager()->getObject(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $optionSetting = $this->createMock(\Amasty\ShopbyBase\Api\Data\OptionSettingInterface::class);
        $option->setLabel('label');
        $option->setValue(1);
        $optionSetting->expects($this->any())->method('getSliderImageUrl')->will($this->returnValue('label'));
        $optionSetting->expects($this->any())->method('getImageUrl')->will($this->returnValue('label'));
        $optionSetting->expects($this->any())->method('getDescription')->will($this->returnValue('label'));
        $optionSetting->expects($this->any())->method('getShortDescription')->will($this->returnValue('label'));
        $optionSetting->expects($this->any())->method('getSmallImageAlt')->will($this->returnValue('label'));
        $this->dataHelper->expects($this->any())->method('isDisplayZero')->will($this->returnValue(true));

        $resultValue = $this->invokeMethod($this->block, 'getItemData', [$option, $optionSetting]);
        $this->assertEquals($result, $resultValue);

        $this->dataHelper->expects($this->any())->method('isDisplayZero')->will($this->returnValue(false));
        $optionSetting->expects($this->any())->method('getValue')->will($this->returnValue(1));
        $resultValue = $this->invokeMethod($this->block, 'getItemData', [$option, $optionSetting]);
        $this->assertEquals($result, $resultValue);
    }

    /**
     * @covers BrandList::sortItems
     */
    public function testSortItems()
    {
        $this->invokeMethod($this->block, 'sortItems', [&$this->notSortedList]);
        $this->assertEquals($this->sortedList, $this->notSortedList);
    }

    /**
     * @covers BrandList::items2letters
     */
    public function testItems2letters()
    {
        $result = $this->invokeMethod($this->block, 'items2letters', [$this->sortedList]);
        $this->assertEquals($this->brandListAfterSort, $result);
    }

    /**
     * @covers BrandList::getAllLetters
     */
    public function testGetAllLetters()
    {
        $action = $this->getMockBuilder(BrandList::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIndex'])
            ->getMockForAbstractClass();

        $action->expects($this->any())->method('getIndex')->will($this->returnValue($this->resultBrandList));
        $items = $this->invokeMethod($action, 'getAllLetters', []);
        $this->assertEquals(['A', 'B', 'C'], $items);
    }

    /**
     * @covers BrandList::getSearchHtml
     */
    public function testGetSearchHtml()
    {
        $this->block->setData('show_search', false);
        $this->assertEquals('', $this->block->getSearchHtml());
    }

    /**
     * @covers BrandList::getSearchHtmlEnabled
     */
    public function testGetSearchHtmlEnabled()
    {
        $data = [['label' =>'a', 'url' => 'a'], ['label' =>'b', 'url' => 'b'], ['label' =>'c', 'url' => 'c']];

        $template = $this->getMockBuilder(\Magento\Framework\View\Element\Template::class)
            ->disableOriginalConstructor()
            ->setMethods(['setBrands', 'setTemplate', 'toHtml'])
            ->getMock();
        $template->expects($this->once())->method('setTemplate')
            ->with('Amasty_ShopbyBrand::brand_search.phtml')
            ->will($this->returnSelf());
        $template->expects($this->once())->method('setBrands')
            ->with('{"a":"a","b":"b","c":"c"}')
            ->will($this->returnSelf());
        $template->expects($this->once())->method('toHtml')
            ->with()
            ->will($this->returnValue(true));

        $layout = $this->createMock(\Magento\Framework\View\Layout::class);
        $layout->expects($this->once())->method('createBlock')
            ->with('Magento\Framework\View\Element\Template', 'ambrands.search')
            ->will($this->returnValue($template));

        $this->setProperty($this->block, 'items', $data);
        $this->setProperty($this->block, '_layout', $layout);
        $this->block->setData('show_search', true);
        $this->assertEquals(true, $this->block->getSearchHtml());
    }

    /**
     * @covers BrandList::isTooltipEnabled
     */
    public function testIsTooltipEnabled()
    {
        $this->brandHelper->expects($this->once())->method('getModuleConfig')
            ->will($this->returnValue('all_brands'));
        $this->assertEquals(true, $this->block->isTooltipEnabled());
    }

    /**
     * @covers BrandList::isTooltipEnabled
     */
    public function testIsTooltipEnabledIfDisable()
    {
        $this->brandHelper->expects($this->once())->method('getModuleConfig')->will($this->returnValue(''));
        $this->assertEquals(false, $this->block->isTooltipEnabled());
    }

    /**
     * @covers BrandList::getTooltipAttribute
     */
    public function testGetTooltipAttribute()
    {
        $this->brandHelper->expects($this->once())->method('getModuleConfig')
            ->will($this->returnValue('all_brands'));
        $this->brandHelper->expects($this->once())->method('generateToolTipContent')
            ->will($this->returnValue(true));
        $this->assertEquals(true, $this->block->getTooltipAttribute([]));
    }

    /**
     * @covers BrandList::getTooltipAttribute
     */
    public function testGetTooltipAttributeWhenDisable()
    {
        $this->brandHelper->expects($this->once())->method('getModuleConfig')->will($this->returnValue(''));
        $this->assertEquals('', $this->block->getTooltipAttribute([]));
    }

    /**
     * @covers BrandList::getConfigValuesPath
     */
    public function testGetConfigValuesPath()
    {
        $this->assertEquals(
            'amshopby_brand/brands_landing',
            $this->invokeMethod($this->block, 'getConfigValuesPath', [])
        );
    }

    /**
     * @covers BrandList::getImageWidth
     */
    public function testGetImageWidth()
    {
        $this->assertEquals(100, $this->block->getImageWidth());
        $this->block->setData('image_width', 200);
        $this->assertEquals(200, $this->block->getImageWidth());
        $this->block->setData('image_width', -200);
        $this->assertEquals(200, $this->block->getImageWidth());
    }

    /**
     * @covers BrandList::getImageHeight
     */
    public function testGetImageHeight()
    {
        $this->assertEquals(50, $this->block->getImageHeight());
        $this->block->setData('image_height', 200);
        $this->assertEquals(200, $this->block->getImageHeight());
        $this->block->setData('image_height', -200);
        $this->assertEquals(200, $this->block->getImageHeight());
    }
}
