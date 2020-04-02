<?php
namespace Magebees\Products\Model\ResourceModel\Importlog;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magebees\Products\Model\Importlog', 'Magebees\Products\Model\ResourceModel\Importlog');
    }
}