<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


use Amasty\ShopbySeo\Plugin\Shopby\Model\UrlBuilder\Adapter;
use Amasty\ShopbySeo\Test\Unit\Traits;

/**
 * Class Adapter
 *
 * @see Adapter
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class AdapterTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const SEO_SUFFIX = '_suffix';

    const TEST_RESULT = 'test';

    /**
     * @covers Adapter::afterGetSuffix
     * @dataProvider afterGetSuffixDataProvider
     */
    public function testAfterGetSuffix($isAddSuffix, $expected)
    {
        $subject = $this->createMock(\Amasty\Shopby\Model\UrlBuilder\Adapter::class);

        $urlHelper = $this->createMock(\Amasty\ShopbySeo\Helper\Url::class);
        $urlHelper->expects($this->any())->method('isAddSuffixToShopby')
            ->willReturn($isAddSuffix);
        $urlHelper->expects($this->any())->method('getSeoSuffix')
            ->willReturn(self::SEO_SUFFIX);

        $adapter = $this->createPartialMock(
            \Amasty\ShopbySeo\Plugin\Shopby\Model\UrlBuilder\Adapter::class,
            []
        );
        $this->setProperty($adapter, 'urlHelper', $urlHelper, Adapter::class);
        $this->assertEquals($expected, $adapter->afterGetSuffix($subject, self::TEST_RESULT));
    }

    /**
     * Data provider for afterGetSuffix test
     * @return array
     */
    public function afterGetSuffixDataProvider()
    {
        return [
            [false, self::TEST_RESULT],
            [true, self::SEO_SUFFIX]
        ];
    }
}
