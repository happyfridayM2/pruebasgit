<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Layer\Cms;

use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;

/**
 * Class Manager
 * @package Amasty\Shopby\Model\Layer\Cms
 */
class Manager
{
    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $layout;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $cmsCollection;

    /**
     * @var  \Magento\Framework\DB\Ddl\Table
     */
    protected $table;

    /**
     * @var bool
     */
    protected $isIndexStorageApplied = false;

    public function __construct(\Magento\Framework\View\LayoutInterface$layout)
    {
        $this->layout = $layout;
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        foreach ($this->layout->getAllBlocks() as $block) {
            if ($block->getProductCollection() instanceof \Magento\Catalog\Model\ResourceModel\Product\Collection) {
                /** @var  \Magento\CatalogWidget\Block\Product\ProductsList $block */

                $collection = $block->getProductCollection();
                $this->cmsCollection = $collection;
                break;
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function setCmsCollection(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection)
    {
        $this->cmsCollection = $collection;
    }

    /**
     * @return bool
     */
    public function isCmsPageNavigation()
    {
        return $this->cmsCollection !== null;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getCmsCollection()
    {
        return $this->cmsCollection;
    }

    /**
     * @param $select
     */
    public function addCmsPageDataToSelect($select)
    {
        $cmsSelect = clone $this->cmsCollection->getSelect();

        $cmsSelect->limit(null);

        $select->joinInner(
            ['blockEntities' => $cmsSelect],
            'search_index.entity_id  = blockEntities.entity_id',
            []
        );
    }

    /**
     * @param \Magento\Framework\DB\Ddl\Table $table
     * @return $this
     */
    public function setIndexStorageTable(\Magento\Framework\DB\Ddl\Table $table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @throws \Zend_Db_Exception
     */
    public function applyIndexStorage(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection)
    {
        if ($this->table && !$this->isIndexStorageApplied) {
            $collection->clear();
            $collection->getSelect()->joinInner(
                [
                    'search_result' => $this->table->getName(),
                ],
                'e.entity_id = search_result.' . TemporaryStorage::FIELD_ENTITY_ID,
                []
            );

            $this->isIndexStorageApplied = true;
        }
    }
}
