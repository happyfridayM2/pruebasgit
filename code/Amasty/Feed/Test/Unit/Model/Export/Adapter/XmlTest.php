<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Test\Unit\Model\Export\Adapter;

use Amasty\Feed\Model\Export\Adapter\Xml;
use Amasty\Feed\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class XmlTest
 *
 * @see Xml
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class XmlTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const VALUE = 'test_value';

    const MODIFIED_VALUE = 'modified_value';

    /**
     * @var Xml|MockObject
     */
    private $xml;

    public function setUp()
    {
        $this->xml = $this->createPartialMock(
            Xml::class,
            ['_modify']
        );
    }

    /**
     * @covers Xml::writeHeader
     */
    public function testWriteHeader()
    {
        $header = '<created_at>{{DATE}}</created_at>';
        $this->setProperty($this->xml, '_header', $header, Xml::class);

        $fileHandler = $this->createPartialMock(
            \Magento\Framework\Filesystem\File\Write::class,
            ['write']
        );
        $fileHandler->expects($this->once())->method('write');
        $this->setProperty($this->xml, '_fileHandler', $fileHandler, Xml::class);

        $this->xml->writeHeader();
    }

    /**
     * @covers Xml::writeFooter
     */
    public function testWriteFooter()
    {
        $footer = 'test_footer';
        $this->setProperty($this->xml, '_footer', $footer, Xml::class);

        $fileHandler = $this->createPartialMock(
            \Magento\Framework\Filesystem\File\Write::class,
            ['write']
        );
        $fileHandler->expects($this->once())->method('write')->with($footer);
        $this->setProperty($this->xml, '_fileHandler', $fileHandler, Xml::class);

        $this->xml->writeFooter();
    }

    /**
     * @covers Xml::_modifyValue
     *
     * @dataProvider modifyValueDataProvider
     */
    public function testModifyValue($modify, $expected)
    {
        $field = [
            'modify' => $modify
        ];

        $this->xml->expects($this->any())->method('_modify')
            ->with(self::VALUE, 'test_type1', 'test_arg1', 'test_arg2')
            ->willReturn(self::MODIFIED_VALUE);

        $result = $this->invokeMethod($this->xml, '_modifyValue', [$field, self::VALUE]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Xml::_formatValue
     *
     * @dataProvider formatValueDataProvider
     */
    public function testFormatValue($value, $expected)
    {
        $field = [
            'modify' => 'yes'
        ];

        $result = $this->invokeMethod($this->xml, '_formatValue', [$field, $value]);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for _modifyValue test
     *
     * @return array
     */
    public function modifyValueDataProvider()
    {
        return [
            ['', self::VALUE],
            ['test_type1:test_arg1^test_arg2', self::MODIFIED_VALUE]
        ];
    }

    /**
     * Data provider for _formatValue test
     *
     * @return array
     */
    public function formatValueDataProvider()
    {
        return [
            [1, 1],
            ['test', '<![CDATA[test]]>']
        ];
    }
}