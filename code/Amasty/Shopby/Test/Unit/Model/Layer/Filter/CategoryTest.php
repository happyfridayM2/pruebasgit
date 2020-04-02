<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Layer\Filter;

use Amasty\Shopby\Model\Layer\Filter\Category;
use Amasty\Shopby\Model\Source\RenderCategoriesLevel;
use Amasty\Shopby\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

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
     * @var \Amasty\Shopby\Helper\FilterSetting
     */
    private $settingHelper;

    /**
     * @var Amasty\Shopby\Model\Layer\Filter\Category
     */
    private $model;

    /**
     * @var \Magento\Framework\Search\RequestInterface
     */
    private $request;

    public function setUp()
    {
        $this->model = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getRenderCategoriesLevel',
                'isRenderAllTree',
                'isMultiselect',
                'getData',
                'buildQueryRequest',
                'getCategoriesTreeDept',
                'getLayer',
                'search'
            ])
            ->getMock();

        $this->request = $this->createMock(\Magento\Framework\Search\RequestInterface::class);
    }

    /**
     * @covers Category::getAlteredQueryResponse
     *
     * @dataProvider getTestDatabase
     *
     * @throws \ReflectionException
     */
    public function testGetAlteredQueryResponse($value, $expectedResult = null)
    {
        $this->model->expects($this->any())->method('getRenderCategoriesLevel')->willReturn(3);
        $this->model->expects($this->any())->method('getCategoriesTreeDept')->willReturn(1);
        $this->model->expects($this->any())->method('isRenderAllTree')->willReturn($value);
        $this->model->expects($this->any())->method('isMultiselect')->will($this->returnValue($value));
        $this->model->expects($this->any())->method('buildQueryRequest')->will($this->returnValue($this->request));

        $currentCategory = $this->getObjectManager()->getObject(\Magento\Catalog\Model\Category::class);
        $currentCategory->setData('id', 2);


        $layer = $this->createMock(\Magento\Catalog\Model\Layer::class);
        $layer->expects($this->any())->method('getCurrentCategory')->will($this->returnValue($currentCategory));
        $this->model->expects($this->any())->method('getLayer')->will($this->returnValue($layer));

        $searchEngine = $this->createMock(\Magento\Search\Model\SearchEngine::class);
        $searchEngine->expects($this->any())->method('search')->will($this->returnValue($expectedResult));

        $rootCategory = $this->getObjectManager()->getObject(\Magento\Catalog\Model\Category::class);
        $rootCategory->setData('id', 1);
        $this->model->expects($this->any())->method('getData')->with('root_category')->will($this->returnValue($rootCategory));
        $this->setProperty($this->model, 'searchEngine', $searchEngine, Category::class);

        $resultOrigMethod = $this->invokeMethod($this->model, 'GetAlteredQueryResponse');
        $this->assertEquals($expectedResult, $resultOrigMethod);
    }

    /**
     * @return array
     */
    public function getTestDatabase()
    {
        return [
            [false],
            [true, 'test'],
        ];
    }
}
