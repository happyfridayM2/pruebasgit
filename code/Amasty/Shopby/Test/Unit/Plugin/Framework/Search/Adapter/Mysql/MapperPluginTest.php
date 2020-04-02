<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Test\Unit\Plugin\Framework\Search\Adapter\Mysql;

use Amasty\Shopby\Model\Inventory\Resolver as InventoryResolver;
use Amasty\Shopby\Plugin\Framework\Search\Adapter\Mysql\MapperPlugin;
use Amasty\Shopby\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Shopby\Test\Unit\Traits\ReflectionTrait;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockResource;
use Magento\Framework\DB\Select;

/**
 * Class MapperPluginTest
 *
 * @see MapperPlugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class MapperPluginTest extends \PHPUnit\Framework\TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @covers MapperPlugin::checkAndModify
     *
     * @dataProvider checkAndModifyDataProvider
     *
     * @throws \ReflectionException
     */
    public function testCheckAndModify($parts, $resultParts)
    {
        /** @var MapperPlugin $model */
        $model = $this->getObjectManager()->getObject(MapperPlugin::class);
        $inventoryResolver = $this->getObjectManager()->getObject(InventoryResolver::class);
        /** @var Select $select */
        $select = $this->getObjectManager()->getObject(Select::class);
        foreach ($parts as $key => $part) {
            $select->setPart($key, $part);
        }

        $stockResource = $this->createMock(StockResource::class);
        $stockResource->expects($this->any())->method('getMainTable')->willReturn('cataloginventory_stock_status');
        $this->setProperty($model, 'stockResource', $stockResource, MapperPlugin::class);
        $this->setProperty($model, 'inventoryResolver', $inventoryResolver, MapperPlugin::class);

        $this->invokeMethod($model, 'checkAndModify', [$select]);

        foreach ($resultParts as $key => $resultPart) {
            $this->assertEquals($resultPart, $select->getPart($key));
        }
    }

    /**
     * Data provider for checkAndModify test
     * @return array
     */
    public function checkAndModifyDataProvider()
    {
        return [
            [
                [
                    Select::FROM => [
                        'stock_status_filter' => [
                            'joinCondition' => 'website_id=34',
                            'tableName' => 'cataloginventory_stock_status'
                        ]
                    ],
                    Select::WHERE => [
                        'test = 34',
                        '`stock_status_filter`.`stock_status` = 1'
                    ]
                ],
                [
                    Select::FROM => [
                        'stock_status_filter' => [
                            'joinCondition' => 'website_id= 0',
                            'tableName' => 'cataloginventory_stock_status'
                        ]
                    ],
                    Select::WHERE => [
                        'test = 34',
                        '`stock_status_filter`.`stock_status` = 1'
                    ]
                ]
            ],
            [
                [
                    Select::FROM => [
                        'stock_status_filter' => [
                            'joinCondition' => 'website_id=34',
                            'tableName' => 'inventory_stock_1'
                        ]
                    ],
                    Select::WHERE => [
                        'test = 34',
                        '`stock_status_filter`.`is_salable` = 1'
                    ]
                ],
                [
                    Select::FROM => [
                        'stock_status_filter' => [
                            'joinCondition' => 'website_id=34',
                            'tableName' => 'inventory_stock_1'
                        ]
                    ],
                    Select::WHERE => [
                        'test = 34',
                        '`stock_status_filter`.`is_salable` = 1'
                    ]
                ]
            ],
            [
                [
                    Select::FROM => [
                        'test' => [
                            'joinCondition' => 'website_id=34',
                            'tableName' => 'inventory_stock_1'
                        ]
                    ]
                ],
                [
                    Select::FROM => [
                        'test' => [
                            'joinCondition' => 'website_id=34',
                            'tableName' => 'inventory_stock_1'
                        ]
                    ]
                ],
            ]
        ];
    }
}
