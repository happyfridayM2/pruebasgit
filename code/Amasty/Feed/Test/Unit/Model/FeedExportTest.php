<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Test\Unit\Model;

use Amasty\Feed\Model\Export\Product;
use Amasty\Feed\Model\Export\ProductFactory;
use Amasty\Feed\Model\Feed;
use Amasty\Feed\Model\FeedExport;
use Amasty\Feed\Model\FeedRepository;
use Amasty\Feed\Test\Unit\Traits;
use Magento\Framework\Event\Manager;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class FeedExportTest
 *
 * @see FeedExport
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class FeedExportTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const STORE_ID = 1;

    const FILENAME = 'test';

    const UTM_PARAMS = [];

    const FORMAT_PRICE_CURRENCY = 'test_currency';

    const FORMAT_PRICE_CURRENCY_SHOW = 'test_currency_show';

    const FORMAT_PRICE_DECIMALS = 'test_price_decimals';

    const FORMAT_PRICE_DECIMAL_POINT = 'test_decimal_point';

    const FORMAT_PRICE_SEPARATOR = 'test_separator';

    const WRITER = 'test_writer';

    const ATTRIBUTES = 'test_attrs';

    const PARENT_ATTRIBUTES = 'test_parent_attrs';

    const EXPORT = 'test_export';

    /**
     * @var FeedExport|MockObject
     */
    private $feedExport;

    private $abstractAdapter;

    public function setUp()
    {
        $this->feedExport = $this->createPartialMock(
            FeedExport::class,
            ['getWriter', 'getAttributes']
        );
    }

    /**
     * @covers FeedExport::export
     */
    public function testExport()
    {
        $feed = $this->initFeedMock();
        $page = 1;
        $productIds = [1];
        $lastPage = 1;
        $this->abstractAdapter = $this->getMockBuilder(AbstractAdapter::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->feedExport->expects($this->once())->method('getWriter')
            ->with($feed, self::FILENAME . '.', $page)->willReturn($this->abstractAdapter);
        $this->feedExport->expects($this->at(1))->method('getAttributes')
            ->with($feed)->willReturn(self::ATTRIBUTES);
        $this->feedExport->expects($this->at(2))->method('getAttributes')
            ->with($feed, true)->willReturn(self::PARENT_ATTRIBUTES);

        $productExport = $this->initProductExportMock($page, $productIds, $lastPage);
        $productExportFactory = $this->createPartialMock(
            ProductFactory::class,
            ['create']
        );
        $productExportFactory->expects($this->once())->method('create')
            ->with(['storeId' => self::STORE_ID])
            ->willReturn($productExport);
        $this->setProperty(
            $this->feedExport,
            'productExportFactory',
            $productExportFactory,
            FeedExport::class
        );

        $feedRepository = $this->createPartialMock(FeedRepository::class, ['save']);
        $feedRepository->expects($this->once())->method('save')
            ->with($feed)->willReturn(null);
        $this->setProperty($this->feedExport, 'feedRepository', $feedRepository, FeedExport::class);

        $feedOutput = $this->createPartialMock(\Amasty\Feed\Model\Filesystem\FeedOutput::class, ['get']);
        $this->setProperty($this->feedExport, 'feedOutput', $feedOutput, FeedExport::class);

        $eventManager = $this->createPartialMock(Manager::class, ['dispatch']);
        $this->setProperty($this->feedExport, 'eventManager', $eventManager, FeedExport::class);

        $result = $this->feedExport->export($feed, $page, $productIds, $lastPage);
        $this->assertEquals(self::EXPORT, $result);
    }

    /**
     * @covers FeedExport::processingCsv
     *
     * @dataProvider processingAttributesDataProvider
     */
    public function testProcessingCsv($parent, $attribute, $expected)
    {
        $feedField = [
            [
            'parent' => 'yes',
            'attribute' => $attribute
            ]
        ];
        $feed = $this->initFeedMock();
        $feed->expects($this->once())->method('getCsvField')
            ->willReturn($feedField);
        $attributes = [
            'test1' => [
                'test2' => ''
            ]
        ];

        $this->feedExport->processingCsv($feed, $attributes, $parent);
        $this->assertEquals($expected, $attributes);
    }

    /**
     * @covers FeedExport::processingXml
     *
     * @dataProvider processingAttributesDataProvider
     */
    public function testProcessingXml($parent, $attribute, $expected)
    {
        $parent = false;
        $xmlContent = '#{/attribute="' . $attribute . '"/parent="yes"}#';
        $feed = $this->initFeedMock();
        $feed->expects($this->once())->method('getXmlContent')
            ->willReturn($xmlContent);
        $attributes = [
            'test1' => [
                'test2' => ''
            ]
        ];

        $this->feedExport->processingXml($feed, $attributes, $parent);
        $this->assertEquals($expected, $attributes);
    }

    /**
     * @return MockObject
     */
    private function initProductExportMock($page, $productIds, $lastPage)
    {
        $productExport = $this->createPartialMock(
            Product::class,
            [
                'setPage',
                'setWriter',
                'setAttributes',
                'setParentAttributes',
                'setMatchingProductIds',
                'setUtmParams',
                'setStoreId',
                'setFormatPriceCurrency',
                'setCurrencyShow',
                'setFormatPriceDecimals',
                'setFormatPriceDecimalPoint',
                'setFormatPriceThousandsSeparator',
                'export'
            ]
        );
        $productExport->expects($this->once())->method('setPage')
            ->with($page)->willReturn($productExport);
        $productExport->expects($this->once())->method('setWriter')
            ->with($this->abstractAdapter)->willReturn($productExport);
        $productExport->expects($this->once())->method('setAttributes')
            ->with(self::ATTRIBUTES)->willReturn($productExport);
        $productExport->expects($this->once())->method('setParentAttributes')
            ->with(self::PARENT_ATTRIBUTES)->willReturn($productExport);
        $productExport->expects($this->once())->method('setMatchingProductIds')
            ->with($productIds)->willReturn($productExport);
        $productExport->expects($this->once())->method('setUtmParams')
            ->with(self::UTM_PARAMS)->willReturn($productExport);
        $productExport->expects($this->once())->method('setStoreId')
            ->with(self::STORE_ID)->willReturn($productExport);
        $productExport->expects($this->once())->method('setFormatPriceCurrency')
            ->with(self::FORMAT_PRICE_CURRENCY)->willReturn($productExport);
        $productExport->expects($this->once())->method('setCurrencyShow')
            ->with(self::FORMAT_PRICE_CURRENCY_SHOW)->willReturn($productExport);
        $productExport->expects($this->once())->method('setFormatPriceDecimals')
            ->with(self::FORMAT_PRICE_DECIMALS)->willReturn($productExport);
        $productExport->expects($this->once())->method('setFormatPriceDecimalPoint')
            ->with(self::FORMAT_PRICE_DECIMAL_POINT)->willReturn($productExport);
        $productExport->expects($this->once())->method('setFormatPriceThousandsSeparator')
            ->with(self::FORMAT_PRICE_SEPARATOR)->willReturn($productExport);
        $productExport->expects($this->once())->method('export')
            ->with($lastPage)->willReturn(self::EXPORT);

        return $productExport;
    }

    /**
     * @return MockObject
     */
    private function initFeedMock()
    {
        $feed = $this->createPartialMock(
            Feed::class,
            ['setGeneratedAt', 'getCsvField', 'getXmlContent']
        );
        $feed->setStoreId(self::STORE_ID);
        $feed->setFilename(self::FILENAME);
        $feed->setUtmParams(self::UTM_PARAMS);
        $feed->setFormatPriceCurrency(self::FORMAT_PRICE_CURRENCY);
        $feed->setFormatPriceCurrencyShow(self::FORMAT_PRICE_CURRENCY_SHOW);
        $feed->setFormatPriceDecimals(self::FORMAT_PRICE_DECIMALS);
        $feed->setFormatPriceDecimalPoint(self::FORMAT_PRICE_DECIMAL_POINT);
        $feed->setFormatPriceThousandsSeparator(self::FORMAT_PRICE_SEPARATOR);

        return $feed;
    }

    /**
     * Data provider for attribute processing test
     *
     * @return array
     */
    public function processingAttributesDataProvider()
    {
        return [
            [false, 'test1|test2', ['test1' => ['test2' => 'test2']]],
            [false, 'test2|test3', ['test1' => ['test2' => '']]],
            [true, 'test1|test2', ['test1' => ['test2' => 'test2']]],
            [true, 'test2|test3', ['test1' => ['test2' => '']]]
        ];
    }
}
