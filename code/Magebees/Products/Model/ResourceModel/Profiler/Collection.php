<?php
namespace Magebees\Products\Model\ResourceModel\Profiler;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magebees\Products\Model\Profiler', 'Magebees\Products\Model\ResourceModel\Profiler');
    }
    public function count()
    {
        $this->load();
        return count($this->_items);
    }
}