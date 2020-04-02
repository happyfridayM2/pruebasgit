<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Ui\DataProvider\Group;

use Amasty\Shopby\Model\ResourceModel\GroupAttr\CollectionFactory;

/**
 * Class DataProvider
 * @package Amasty\Shopby\Ui\DataProvider\Group
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Amasty\Shopby\Helper\Group
     */
    private $groupHelper;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Amasty\Shopby\Helper\Group $groupHelper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->groupHelper = $groupHelper;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        $items = $this->getCollection()->toArray();
        foreach ($items['items'] as &$item) {
            $item['name'] = $this->groupHelper->chooseGroupLabel($item['name']);
        }

        return $items;
    }
}
