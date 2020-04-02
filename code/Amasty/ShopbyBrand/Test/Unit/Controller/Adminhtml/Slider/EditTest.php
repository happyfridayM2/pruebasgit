<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Test\Unit\Controller\Adminhtml\Slider;

use Amasty\ShopbyBrand\Controller\Adminhtml\Slider\Edit;
use Amasty\ShopbyBrand\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class EditTest
 *
 * @see Edit
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Edit
     */
    private $controller;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Amasty\ShopbyBase\Helper\OptionSetting
     */
    private $settingHelper;

    /**
     * @var \Amasty\ShopbyBase\Api\Data\OptionSettingInterface
     */
    private $optionSetting;

    public function setUp()
    {
        $context = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->settingHelper = $this->createMock(\Amasty\ShopbyBase\Helper\OptionSetting::class);
        $this->optionSetting = $this->createMock(\Amasty\ShopbyBase\Api\Data\OptionSettingInterface::class);

        $this->controller = $this->getObjectManager()->getObject(
            Edit::class,
            [
                'context' => $context,
                'settingHelper' => $this->settingHelper,
            ]
        );
        $this->setProperty($this->controller, '_request', $this->request);
    }

    /**
     * @covers Edit::loadSettingModel
     */
    public function testLoadSettingModel()
    {
        $this->request->expects($this->at(0))->method('getParam')->with('filter_code')->willReturn('test');
        $this->request->expects($this->at(1))->method('getParam')->with('option_id')->willReturn(1);
        $this->request->expects($this->at(2))->method('getParam')->with('store', 0)->willReturn(2);
        $optionSetting = $this->getObjectManager()->getObject(\Amasty\ShopbyBase\Model\OptionSetting::class);
        $optionSetting->setId(1);
        $this->settingHelper->expects($this->any())->method('getSettingByValue')->willReturn($optionSetting);
        $result = $this->invokeMethod($this->controller, 'loadSettingModel');
        $this->assertEquals(2, $result->getCurrentStoreId());
    }

    /**
     * @covers Edit::loadSettingModel
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     *
     */
    public function testLoadSettingModelWithoutData()
    {
        $this->request->expects($this->at(0))->method('getParam')->with('filter_code')->willReturn(false);
        $this->request->expects($this->at(1))->method('getParam')->with('option_id')->willReturn(false);
        $this->request->expects($this->at(2))->method('getParam')->with('store', 0)->willReturn(false);
        $this->invokeMethod($this->controller, 'loadSettingModel');
    }

    /**
     * @covers Edit::loadSettingModel
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     *
     */
    public function testLoadSettingModelWithoutModel()
    {
        $this->settingHelper->expects($this->any())->method('getSettingByValue')->willReturn($this->optionSetting);
        $this->request->expects($this->at(0))->method('getParam')->with('filter_code')->willReturn('test');
        $this->optionSetting->expects($this->any())->method('getId')->willReturn(0);
        $this->setProperty($this->controller, '_request', $this->request);
        $this->invokeMethod($this->controller, 'loadSettingModel');
    }
}
