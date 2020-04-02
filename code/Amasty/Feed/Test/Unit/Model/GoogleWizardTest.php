<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Test\Unit\Model;

use Magento\Backend\Model\Session;
use Amasty\Feed\Model\GoogleWizard\ElementFactory;
use Amasty\Feed\Model\GoogleWizard;
use Amasty\Feed\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class GoogleWizardTest
 *
 * @see GoogleWizard
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class GoogleWizardTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const FORMAT_PRICE_CURRENCY_USD = [
        'format_price_currency' => 'USD'
    ];
    const FORMAT_PRICE_CURRENCY_EUR = [
        'format_price_currency' => 'EUR'
    ];
    const FORMAT_PRICE_CURRENCY_RUB = [
        'format_price_currency' => 'RUB'
    ];
    const REQUEST_DATA = [
        'optional' => [
            'mpn' => [
                'attribute' => 'value'
            ]
        ]
    ];

    /**
     * @covers GoogleWizard::getCurrency
     *
     * @dataProvider getCurrencyDataProvider
     *
     * @throws \ReflectionException
     */
    public function testGetCurrency($format, $expectedResult)
    {
        /** @var Session $session */
        $session = $this->createPartialMock(Session::class, ['getAmastyFeedGoogleRequestData']);
        $session->expects($this->any())->method('getAmastyFeedGoogleRequestData')->willReturn(1);
        /** @var \Amasty\Base\Model\Serializer $serializer */
        $serializer = $this->createMock(\Amasty\Base\Model\Serializer::class);
        $serializer->expects($this->any())->method('unserialize')->willReturn($format);
        /** @var GoogleWizard $model */
        $model = $this->getObjectManager()->getObject(GoogleWizard::class, [
            'session' => $session,
            'serializer' => $serializer
        ]);

        $this->assertEquals($expectedResult, $model->getCurrency());
    }

    /**
     * Data provider for getCurrency test
     * @return array
     */
    public function getCurrencyDataProvider()
    {
        return [
            [self::FORMAT_PRICE_CURRENCY_USD, 'USD'],
            [self::FORMAT_PRICE_CURRENCY_EUR, 'EUR'],
            [self::FORMAT_PRICE_CURRENCY_RUB, 'RUB'],
            ['wrongFormat', null],
        ];
    }

    /**
     * @covers GoogleWizard::getBasicAttributes
     *
     * @dataProvider getBasicAttributesDataProvider
     *
     * @throws \ReflectionException
     */
    public function testGetBasicAttributes($key)
    {
        /** @var ElementFactory|MockObject $googleElement */
        $googleElement = $this->createPartialMock(ElementFactory::class, ['create', 'getRequired']);
        $googleElement->expects($this->any())->method('getRequired')->willReturn(true);
        /** @var GoogleWizard|MockObject $model */
        $model = $this->createPartialMock(GoogleWizard::class, ['loadAttribute', 'setAttributeData']);

        $feedMock = $this->createPartialMock(\Amasty\Feed\Model\Feed::class, []);
        $this->setProperty($model, 'feed', $feedMock, GoogleWizard::class);

        $model->expects($this->any())->method('loadAttribute')->willReturn($googleElement);
        $model->expects($this->any())->method('setAttributeData');

        $this->assertArrayHasKey($key, $model->getBasicAttributes());
    }

    /**
     * Data provider for getBasicAttributes test
     * @return array
     */
    public function getBasicAttributesDataProvider()
    {
        return [
            ['id'],
            ['title'],
            ['description'],
            ['type'],
            ['image'],
            ['price'],
            ['size'],
            ['color'],
            ['tax'],
        ];
    }

    /**
     * @covers GoogleWizard::canUseIdentifierExists
     *
     * @throws \ReflectionException
     */
    public function testCanUseIdentifierExists()
    {
        /** @var GoogleWizard|MockObject $model */
        $model = $this->createMock(GoogleWizard::class);

        $result = $this->invokeMethod($model, 'canUseIdentifierExists', [self::REQUEST_DATA]);
        $this->assertTrue($result);
    }
}
