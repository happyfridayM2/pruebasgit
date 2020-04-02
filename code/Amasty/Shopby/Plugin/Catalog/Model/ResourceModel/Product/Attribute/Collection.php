<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Attribute;

use \Amasty\Shopby\Model\Search\RequestGenerator;

/**
 * Class Collection
 * @package Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Product\Attribute
 */
class Collection
{
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $subject
     * @param \Closure $closure
     * @param $column
     * @param $value
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundGetItemByColumnValue(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $subject,
        \Closure $closure,
        $column,
        $value
    ) {
        if ($column == 'attribute_code'
            && ($pos = strpos($value, RequestGenerator::FAKE_SUFFIX)) !== false
        ) {
            $value = substr($value, 0, $pos);
        }
        return $closure($column, $value);
    }
}
