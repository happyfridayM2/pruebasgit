<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Test\Unit\Block\Adminhtml\Form\Renderer\Fieldset;

use Amasty\ShopbyBase\Block\Adminhtml\Form\Renderer\Fieldset\Element;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\ShopbyBase\Test\Unit\Traits;

/**
 * Class ElementTest
 *
 * @see Element
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ElementTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Element|MockObject
     */
    private $model;

    /**
     * @var \Amasty\ShopbyBase\Model\OptionSetting
     */
    private $dataObject;

    /**
     * @var boolean|null
     */
    private $boolFlag = null;

    /**
     * @var string
     */
    private $elementName = 'elementName';

    /**
     * @throws \ReflectionException
     */
    protected function setUp()
    {
        $this->dataObject = $this->getObjectManager()->getObject(\Amasty\ShopbyBase\Model\OptionSetting::class);
        $this->dataObject->setData('current_store_id', 1);

        $element =
            $this->getMockForAbstractClass(\Magento\Framework\Data\Form\Element\AbstractElement::class, [], '', false, false, true, ['getName', 'setDisabled']);
        $element->expects($this->any())->method('getName')->willReturnReference($this->elementName);
        $element->expects($this->any())->method('setDisabled')->willReturnCallback(
            function ($value) {
                $this->boolFlag = $value;
            }
        );

        $this->model = $this->createPartialMock(Element::class, ['getDataObject']);
        $this->model->expects($this->any())->method('getDataObject')->willReturnReference($this->dataObject);
        $this->setProperty($this->model, '_element', $element, Element::class);
    }

    /**
     * @covers Element::getScopeLabel
     */
    public function testGetScopeLabel()
    {
        $this->assertEquals(Element::SCOPE_LABEL, $this->model->getScopeLabel());
    }

    /**
     * @covers Element::usedDefault
     */
    public function testUsedDefault()
    {
        $this->assertTrue($this->model->usedDefault());

        $this->dataObject->setData('elementName_use_default', false);
        $this->assertFalse($this->model->usedDefault());
    }

    /**
     * @covers Element::checkFieldDisable
     */
    public function testCheckFieldDisable()
    {
        $this->model->checkFieldDisable();
        $this->assertTrue($this->boolFlag);
    }

    /**
     * @covers Element::canDisplayUseDefault
     */
    public function testCanDisplayUseDefault()
    {
        //Case 1: `current_store_id` is positive value & element name is not in array
        $this->assertTrue($this->model->canDisplayUseDefault());

        //Case 2: `current_store_id` is negative value & element name is not in array
        $this->dataObject->setData('current_store_id', false);
        $this->assertFalse($this->model->canDisplayUseDefault());

        //Case 2: `current_store_id` is negative value & element name is in array
        $this->elementName = 'title';
        $this->assertTrue($this->model->canDisplayUseDefault());
    }
}
