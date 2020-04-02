<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Test\Unit\Helper;

use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory as OptionCollectionFactory;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\Collection;
use Amasty\ShopbyBrand\Helper\Data;
use Amasty\ShopbyBrand\Test\Unit\Traits;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\ShopbyBase\Model\OptionSetting;

/**
 * Class DataTest
 *
 * @see Data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var MockObject|AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var MockObject|Collection
     */
    private $optionCollection;

    public function setUp()
    {
        $context = $this->createMock(\Magento\Framework\App\Helper\Context::class);
        $urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $moduleManager = $this->createMock(\Magento\Framework\Module\Manager::class);
        $productUrl = $this->createMock(ProductUrl::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeMock = $this->createMock(StoreInterface::class);
        $optionCollectionFactory = $this->createMock(OptionCollectionFactory::class);
        $this->optionCollection = $this->createMock(Collection::class);
        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->attributeRepository = $this->createMock(AttributeRepository::class);

        $context->expects($this->any())->method('getScopeConfig')->willReturn($this->scopeConfig);
        $context->expects($this->any())->method('getModuleManager')->willReturn($moduleManager);
        $context->expects($this->any())->method('getUrlBuilder')->willReturn($urlBuilder);
        $moduleManager->expects($this->any())->method('isEnabled')->willReturn(true);
        $optionCollectionFactory->expects($this->any())->method('create')->willReturn($this->optionCollection);
        $storeMock->expects($this->any())->method('getId')->willReturn(1);
        $storeManager->expects($this->any())->method('getStore')->willReturn($storeMock);
        $this->optionCollection->expects($this->any())->method('addFieldToFilter')->willReturn($this->optionCollection);
        $urlBuilder->expects($this->any())->method('getBaseUrl')->willReturn('');
        $productUrl->expects($this->any())->method('formatUrlKey')->willReturnArgument(0);


        $this->helper = $this->getObjectManager()->getObject(
            Data::class,
            [
                'context' => $context,
                'attributeRepository' => $this->attributeRepository,
                'productUrl' => $productUrl,
                'storeManager' => $storeManager,
                'optionCollectionFactory' => $optionCollectionFactory,
            ]
        );
    }

    /**
     * @covers Data::getIdentifier
     */
    public function testGetIdentifier()
    {
        $this->dataForGetIdentifier('test', 'test');
    }

    /**
     * @covers Data::getIdentifierForMultistore
     */
    public function testGetIdentifierForMultistore()
    {
        $this->dataForGetIdentifier('test1|test2|test3', 'test1');
    }

    private function dataForGetIdentifier($data, $result)
    {
        $this->scopeConfig->expects($this->any())->method('getValue')->willReturn($data);
        $this->assertEquals($result, $this->invokeMethod($this->helper, 'getIdentifier', [1]));
    }

    /**
     * @covers Data::getCurrentBrandAttributeValues
     */
    public function testGetCurrentBrandAttributeValues()
    {
        $this->addOptions();
        $result = $this->invokeMethod($this->helper, 'getCurrentBrandAttributeValues', ['test']);
        $this->assertEquals(['option1', 'option2'], $result);
    }

    /**
     * @covers Data::getBrandAliases
     */
    public function testGetBrandAliases()
    {
        $data = ['test'];
        $this->scopeConfig->expects($this->any())->method('getValue')->willReturn('_');
        $this->optionCollection->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator([]));
        $this->addOptions();

        $this->assertEquals(['option2' => 'option_2'], $this->helper->getBrandAliases());

        $this->setProperty($this->helper, 'brandAliases', $data);
        $this->assertEquals($data, $this->helper->getBrandAliases());
    }

    /**
     * @covers Data::getBrandAliasesWithoutAttrCode
     */
    public function testGetBrandAliasesWithoutAttrCode()
    {
        $this->scopeConfig->expects($this->any())->method('getValue')->willReturn('');
        $this->assertEquals([], $this->helper->getBrandAliases());
    }

    /**
     * @covers Data::getBrandAliasesWithoutOptions
     */
    public function testGetBrandAliasesWithoutOptions()
    {
        $this->scopeConfig->expects($this->any())->method('getValue')->willReturn('test');
        $attribute = $this->createMock(\Magento\Catalog\Api\Data\ProductAttributeInterface::class);
        $attribute->expects($this->any())->method('getOptions')->willReturn([]);
        $this->attributeRepository->expects($this->any())->method('get')->willReturn($attribute);

        $this->assertEquals([], $this->helper->getBrandAliases());
    }

    private function addOptions()
    {
        $attribute = $this->createMock(\Magento\Catalog\Api\Data\ProductAttributeInterface::class);
        $option1 = $this->getObjectManager()->getObject(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $option2 = $this->getObjectManager()->getObject(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $attribute->expects($this->any())->method('getOptions')->willReturn([$option1, $option2]);
        $option1->setValue('option1')->setLabel('option-1');
        $option2->setValue('option2')->setLabel('option-2');
        $this->attributeRepository->expects($this->any())->method('get')->willReturn($attribute);
    }

    /**
     * @covers Data::getBrandUrl
     */
    public function testGetBrandUrl()
    {
        $this->scopeConfig->expects($this->any())->method('getValue')->willReturn('test');
        $option = $this->getObjectManager()->getObject(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $option->setValue('option');
        $this->setProperty($this->helper, 'brandAliases', ['alias1', 'alias2', 'alias3']);

        $this->assertEquals('#', $this->helper->getBrandUrl($option));

        $this->setProperty(
            $this->helper,
            'brandAliases',
            ['option' => 'alias1', 'option1' => 'alias2', 'option2' => 'alias3']
        );

        $this->assertEquals('test/alias1', $this->helper->getBrandUrl($option));
    }

    /**
     * @covers Data::getBrandUrlWithoutUrlKey
     */
    public function testGetBrandUrlWithoutUrlKey()
    {
        $this->scopeConfig->expects($this->any())->method('getValue')->willReturn('');
        $option = $this->getObjectManager()->getObject(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $option->setValue('option');
        $this->setProperty(
            $this->helper,
            'brandAliases',
            ['option' => 'alias1', 'option1' => 'alias2', 'option2' => 'alias3']
        );

        $this->assertEquals('alias1', $this->helper->getBrandUrl($option));
    }

    /**
     * @covers Data::getStoreAliases
     */
    public function testGetStoreAliases()
    {
        $this->scopeConfig->expects($this->any())->method('getValue')->willReturn('_');
        $optionSetting1 = $this->getObjectManager()->getObject(OptionSetting::class);
        $optionSetting2 = $this->getObjectManager()->getObject(OptionSetting::class);
        $optionSetting1->setUrlAlias('option-1')->setValue(1);
        $optionSetting2->setUrlAlias('option_2')->setValue(2);
        $this->optionCollection->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$optionSetting1, $optionSetting2]));
        $result = $this->invokeMethod($this->helper, 'getStoreAliases', [[], 1]);
        $this->assertEquals([1 => 'option_1', 2 => 'option_2'], $result);
    }
}
