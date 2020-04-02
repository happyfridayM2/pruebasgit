<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Ui\DataProvider\Listing;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Amasty\ShopbyBrand\Model\ResourceModel\Slider\Grid\Collection;
use Magento\Framework\Api\Filter;

/**
 * Class DataProvider
 *
 * @package Amasty\ShopbyBrand\Ui\DataProvider\Listing
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    private $mappedFields = [
        'meta_title' => ['main_table.meta_title', 'option.value'],
        'title' => ['main_table.title', 'option.value']
    ];

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
    }

    /**
     * TODO: Unit
     * @param Filter $filter
     * @return mixed|void
     */
    public function addFilter(Filter $filter)
    {
        $condition = [$filter->getConditionType() => $filter->getValue()];

        if (array_key_exists($filter->getField(), $this->mappedFields)) {
            $mappedFields = $this->mappedFields[$filter->getField()];
            $condition = array_fill(0, count($mappedFields), $condition);
            $filter->setField($mappedFields);
        }

        $this->getCollection()->addFieldToFilter(
            $filter->getField(),
            $condition
        );
    }

    /**
     * @return \int[]
     */
    public function getAllIds()
    {
        $this->getCollection();
        return parent::getAllIds();
    }
}
