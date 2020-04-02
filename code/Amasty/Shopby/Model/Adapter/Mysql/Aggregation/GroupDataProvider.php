<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Adapter\Mysql\Aggregation;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;

/**
 * Class GroupDataProvider
 * @package Amasty\Shopby\Model\Adapter\Mysql\Aggregation
 */
class GroupDataProvider implements DataProviderInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var Config
     */
    private $eavConfig;

    public function __construct(
        Config $eavConfig,
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        Session $customerSession
    ) {
        $this->eavConfig = $eavConfig;
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->customerSession = $customerSession;
    }

    /**
     * @param Select $select
     * @return array
     */
    public function execute(Select $select)
    {
        return $this->resource->getConnection()->fetchAssoc($select);
    }

    /**
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param Table $entityIdsTable
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Exception
     */
    public function getDataSet(
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {
        $currentScope = $this->scopeResolver->getScope($dimensions['scope']->getValue())->getId();
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $bucket->getField());
        $select = $this->resource->getConnection()->select();
        $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();
        $table = $this->resource->getTableName('catalog_product_index_eav');
        $select->from(['main_table' => $table], ['entity_id', 'value'])
            ->where('main_table.attribute_id = ?', $attribute->getAttributeId())
            ->where('main_table.store_id = ? ', $currentScopeId)
            ->where('main_table.value IN (?)', $dimensions['groups']);
        $select->joinInner(
            ['entities' => $entityIdsTable->getName()],
            'main_table.entity_id  = entities.entity_id',
            []
        );

        return $select;
    }
}
