<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


use Amasty\ShopbySeo\Helper\Meta;
use Amasty\ShopbySeo\Test\Unit\Traits;
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

    const REQUEST_VAR = 'request_var';

    const TEST_VALUES = 'test1,test2';

    const TEST_TAG_VALUE = true;

    /**
     * @var Meta|MockObject
     */
    private $meta;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\ShopbyBase\Model\FilterSetting
     */
    private $filterSetting;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\FilterInterface|MockObject
     */
    private $filter;

    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    private $dataHelper;

    public function setUp()
    {
        $this->meta = $this->getMockBuilder(Meta::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->request = $this->getObjectManager()
            ->getObject(\Magento\Framework\App\Request\Http::class);

        $this->filterSetting = $this->getObjectManager()
            ->getObject(\Amasty\ShopbyBase\Model\FilterSetting::class);

        $this->filter = $this->createMock(\Magento\Catalog\Model\Layer\Filter\FilterInterface::class);

        $this->setProperty($this->meta, '_request', $this->request, Meta::class);

        $this->dataHelper = $this->createMock(\Amasty\Shopby\Helper\Data::class);
        $this->dataHelper->expects($this->any())->method('getSelectedFiltersSettings')->willReturn([]);

    }

    /**
     * @covers Meta::getTagByData
     * @dataProvider getTagByDataDataProvider
     */
    public function testGetTagByData($tagKey, $settingMode, $tagValue, $expected)
    {
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->setProperty($this->meta, 'dataHelper', $this->dataHelper, Meta::class);
        $this->filter->expects($this->any())->method('getRequestVar')->willReturn(self::REQUEST_VAR);
        $scopeConfig->expects($this->any())->method('isSetFlag')->willReturn(true);
        $this->request->setParam(self::REQUEST_VAR, self::TEST_VALUES);
        $this->filterSetting->setData($tagKey, $settingMode);
        $data = [
            'setting' => $this->filterSetting,
            'filter' => $this->filter
        ];

        $this->setProperty($this->meta, 'scopeConfig', $scopeConfig, Meta::class);

        $result = $this->meta->getTagByData($tagKey, $tagValue, $data);
        $this->assertEquals($expected, $result);
    }

    public function getTagByDataDataProvider()
    {
        return [
            ['index_mode', 2, self::TEST_TAG_VALUE, self::TEST_TAG_VALUE],
            ['index_mode', 1, self::TEST_TAG_VALUE, false ],
            ['index_mode', 0, self::TEST_TAG_VALUE, false],
            ['follow_mode', 2, self::TEST_TAG_VALUE, self::TEST_TAG_VALUE]
        ];
    }
}
