<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\ResourceModel\Feed;

/**
 * Class Feed Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Feed\Model\Feed::class, \Amasty\Feed\Model\ResourceModel\Feed::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
