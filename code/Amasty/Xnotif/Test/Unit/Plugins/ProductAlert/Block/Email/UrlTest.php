<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Xnotif\Test\Unit\Plugins\ProductAlert\Block\Email;

use Amasty\Xnotif\Plugins\ProductAlert\Block\Email\Url;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\Xnotif\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Xnotif\Test\Unit\Traits\ReflectionTrait;

/**
 * Class UrlTest
 *
 * @see Url
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UrlTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectManagerTrait;

    private $plugin;

    private $registry;

    protected function setUp()
    {
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);
        $urlHash = $this->createMock(\Amasty\Xnotif\Model\UrlHash::class);

        $urlHash->expects($this->any())->method('getHash')->willReturn('test');

        $this->plugin = $this->getObjectManager()->getObject(
            Url::class,
            [
                'registry' => $this->registry,
                'urlHash' => $urlHash,
            ]
        );
    }

    /**
     * @covers Url::getType
     * @dataProvider getDataForCheckType
     * @throws \ReflectionException
     */
    public function testGetType($value, $result)
    {
        $this->assertEquals($result, $this->invokeMethod($this->plugin, 'getType', [$value]));
    }

    public function getDataForCheckType()
    {
        return [
            [
                $this->getObjectManager()->getObject(\Magento\ProductAlert\Block\Email\Price::class),
                'price'
            ],
            [
                $this->getObjectManager()->getObject(\Magento\ProductAlert\Block\Email\Stock::class),
                'stock'
            ],
            [
                null,
                null
            ],
        ];
    }

    /**
     * @covers Url::beforeGetUrl
     * @throws \ReflectionException
     */
    public function testBeforeGetUrl()
    {
        $data = [
            'guest' => 1,
            'email' => 'email'
        ];
        $result = [
            'test',
            [
                'product_id' => 1,
                'email' => 'email',
                'hash' => 'test',
                'type' => 'price'
            ]
        ];
        $this->registry->expects($this->any())->method('registry')->will($this->onConsecutiveCalls(false, [], $data));

        $object = $this->getObjectManager()->getObject(\Magento\ProductAlert\Block\Email\Price::class);

        $this->setProperty($this->plugin, 'productId', 1);

        $this->assertEquals(['test', []], $this->plugin->beforeGetUrl($object, 'test'));
        $this->assertEquals(['test', []], $this->plugin->beforeGetUrl(null, 'test'));
        $this->assertEquals($result, $this->plugin->beforeGetUrl($object, 'test'));
    }
}
