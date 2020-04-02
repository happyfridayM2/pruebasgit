<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Test\Unit\Block\Widget\Form\Element;

use Amasty\ShopbyBase\Block\Widget\Form\Element\Dependence;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Amasty\ShopbyBase\Test\Unit\Traits;

/**
 * Class DependenceTest
 *
 * @see Dependence
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DependenceTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;

    /**
     * @var \Amasty\ShopbyBase\Model\Source\DisplayMode|MockObject
     */
    private $displayModeSource;

    /**
     * @var Dependence
     */
    private $block;

    protected function setUp()
    {
        $this->displayModeSource = $this->createMock(\Amasty\ShopbyBase\Model\Source\DisplayMode::class);

        $this->block = $this->getObjectManager()->getObject(
            Dependence::class,
            ['displayModeSource' => $this->displayModeSource]
        );
    }

    /**
     * @covers Dependence::getPreparedOptions
     */
    public function testGetPreparedOptions()
    {
        $this->displayModeSource->expects($this->once())
            ->method('getNotices')
            ->will($this->returnValue([]));
        $this->displayModeSource->expects($this->once())
            ->method('getEnabledTypes')
            ->will($this->returnValue([]));
        $this->displayModeSource->expects($this->once())
            ->method('getChangeLabels')
            ->will($this->returnValue([]));

        $result = $this->block->getPreparedOptions();

        $this->assertArrayHasKey('levels_up', $result);
        $this->assertArrayHasKey('notices', $result);
        $this->assertArrayHasKey('enabled_types', $result);
        $this->assertArrayHasKey('change_labels', $result);
    }

    /**
     * @covers Dependence::addGroupValues
     */
    public function testAddGroupValues()
    {
        $this->block->addGroupValues('fieldName', 'fieldNameFrom', [], []);
        $groupValues = $this->block->getGroupValues();
        $this->assertArrayHasKey('fieldName', $groupValues);
        $this->assertArrayHasKey('fieldNameFrom', $groupValues['fieldName']);
        $this->assertArrayHasKey('dependencies', $groupValues['fieldName']['fieldNameFrom']);
        $this->assertArrayHasKey('values', $groupValues['fieldName']['fieldNameFrom']);
    }

    /**
     * @covers Dependence::addFieldsets
     */
    public function testAddFieldsets()
    {
        $this->block->addFieldsets('fieldSetName', 'fieldNameFrom', []);
        $fieldSets = $this->block->getFieldSets();
        $this->assertArrayHasKey('fieldSetName', $fieldSets);
        $this->assertArrayHasKey('fieldNameFrom', $fieldSets['fieldSetName']);
    }

    /**
     * @covers Dependence::addFieldToGroup
     */
    public function testAddFieldToGroup()
    {
        $this->block->addFieldToGroup('fieldName', []);
        $this->assertArrayHasKey('fieldName', $this->block->getGroupFields());
    }
}
