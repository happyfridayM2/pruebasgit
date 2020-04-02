<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


use Amasty\ShopbyBase\Helper\OptionSetting;
use Amasty\ShopbyBase\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class OptionSetting
 *
 * @see OptionSetting
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class OptionSettingTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const FEATURED_OPTIONS = [
        'option1' => 'option1',
        'option2' => 'option2'
    ];

    /**
     * @var OptionSetting|MockObject
     */
    private $optionSettingHelper;

    /**
     * @var \Amasty\ShopbyBase\Model\OptionSettingRepository|MockObject
     */
    private $optionSettingRepository;

    /**
     * @var \Amasty\ShopbyBase\Model\OptionSetting
     */
    private $optionSetting;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|MockObject
     */
    private $attribute;

    public function setUp()
    {
        $this->optionSetting = $this->getObjectManager()
            ->getObject(\Amasty\ShopbyBase\Model\OptionSetting::class);
    }

    private function initOptionSettingHelper(array $methods = [])
    {
        $this->optionSettingHelper = $this->getMockBuilder(OptionSetting::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMockForAbstractClass();

        $this->optionSettingRepository = $this->createMock(
            \Amasty\ShopbyBase\Model\OptionSettingRepository::class
        );

        $this->attribute = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['getOptions']
        );

        $repository = $this->createMock(\Magento\Catalog\Model\Product\Attribute\Repository::class);
        $repository->expects($this->any())->method('get')->willReturn($this->attribute);

        $this->setProperty(
            $this->optionSettingHelper,
            'optionSettingRepository',
            $this->optionSettingRepository,
            OptionSetting::class
        );
        $this->setProperty(
            $this->optionSettingHelper,
            'repository',
            $repository,
            OptionSetting::class
        );
    }

    /**
     * @covers OptionSetting::getSettingByValue
     * @dataProvider getSettingByValueDataProvider
     */
    public function testGetSettingByValue($optionSettingId)
    {
        $this->initOptionSettingHelper(['applyDataFromOption']);

        $value = 1;
        $filterCode = 'attr_test';
        $storeId = 0;

        $this->optionSetting->setId($optionSettingId);

        $this->optionSettingRepository->expects($this->any())->method('getByParams')
            ->willReturn($this->optionSetting);
        $this->optionSettingHelper->expects($this->any())->method('applyDataFromOption')
            ->with($this->attribute, $value, $this->optionSetting)
            ->willReturn($this->optionSetting);

        $result = $this->optionSettingHelper->getSettingByValue($value, $filterCode, $storeId);
        $this->assertInstanceOf(
            \Amasty\ShopbyBase\Api\Data\OptionSettingInterface::class,
            $result
        );
    }

    /**
     * @covers OptionSetting::applyDataFromOption
     */
    public function testApplyDataFromOption()
    {
        $this->initOptionSettingHelper();
        $value = 'test2';
        $optionOne = $this->getObjectManager()->getObject(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $optionOne->setValue('test1');
        $optionTwo = $this->getObjectManager()->getObject(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $optionTwo->setValue($value);

        $attrOptions = [
            $optionOne, $optionTwo
        ];
        $this->attribute->expects($this->any())->method('getOptions')
            ->willReturn($attrOptions);

        $result = $this->optionSettingHelper->applyDataFromOption($this->attribute, $value, $this->optionSetting);
        $this->assertInstanceOf(
            \Amasty\ShopbyBase\Api\Data\OptionSettingInterface::class,
            $result
        );
    }

    /**
     * @covers OptionSetting::getAllFeaturedOptionsArray
     */
    public function testGetAllFeaturedOptionsArray()
    {
        $this->initOptionSettingHelper(['getCurrentStoreId']);

        $optionSettingResource = $this->createMock(\Amasty\ShopbyBase\Model\ResourceModel\OptionSetting::class);
        $optionSettingResource->expects($this->any())->method('getAllFeaturedOptionsArray')
            ->willReturn(self::FEATURED_OPTIONS);
        $this->setProperty(
            $this->optionSettingHelper,
            'optionSettingResource',
            $optionSettingResource,
            OptionSetting::class
        );
        $this->optionSettingHelper->expects($this->any())->method('getCurrentStoreId')
            ->willReturn(1);

        $resultOne = $this->optionSettingHelper->getAllFeaturedOptionsArray();
        $resultTwo = $this->optionSettingHelper->getAllFeaturedOptionsArray();
        $this->assertEquals($resultOne, $resultTwo);
    }

    /**
     * @return array
     */
    public function getSettingByValueDataProvider()
    {
        return [
            [1],
            [null]
        ];
    }
}
