<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\PageSpeedOptimizer\Model\Bundle\Bundle::class,
            \Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\Bundle::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
