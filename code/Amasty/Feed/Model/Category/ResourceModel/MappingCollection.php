<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Category\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Catalog Category MappingCollection
 *
 * @package Amasty\Feed
 */
class MappingCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\Feed\Model\Category\Mapping::class,
            \Amasty\Feed\Model\Category\ResourceModel\Mapping::class
        );
    }
}
