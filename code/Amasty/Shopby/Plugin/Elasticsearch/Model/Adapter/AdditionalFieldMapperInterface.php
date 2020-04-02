<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter;

/**
 * Interface AdditionalFieldMapperInterface
 * @package Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter
 */
interface AdditionalFieldMapperInterface
{
    /**
     * @return array
     */
    public function getAdditionalAttributeTypes();

    /**
     * @param array $context
     * @return string
     */
    public function getFiledName($context);
}
