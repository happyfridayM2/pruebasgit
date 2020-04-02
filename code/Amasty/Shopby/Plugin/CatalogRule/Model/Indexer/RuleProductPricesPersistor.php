<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\CatalogRule\Model\Indexer;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Class RuleProductPricesPersistor
 * @package Amasty\Shopby\Plugin\CatalogRule\Model\Indexer
 */
class RuleProductPricesPersistor
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    public function __construct(IndexerRegistry $indexerRegistry)
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute($subject, $result)
    {
        if ($result) {
            $this->indexerRegistry->get(Processor::INDEXER_ID)->invalidate();
        }

        return $result;
    }
}
