<?php

/**
 * Created by PhpStorm.
 * User: migue
 * Date: 23/05/2017
 * Time: 17:05
 */

namespace Oct8ne\Oct8ne\Model\ResourceModel\Oct8neHistory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_idFieldName = 'id_oct8nehistory';
    protected $_eventPrefix = 'oct8ne_oct8ne_oct8nehistory_collection';
    protected $_eventObject = 'oct8nehistory_collection';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('Oct8ne\Oct8ne\Model\Oct8neHistory', 'Oct8ne\Oct8ne\Model\ResourceModel\Oct8neHistory');
    }

}