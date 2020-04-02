<?php
namespace Magebees\Navigationmenu\Model\ResourceModel\Menucreator;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magebees\Navigationmenu\Model\Menucreator', 'Magebees\Navigationmenu\Model\ResourceModel\Menucreator');
    }
}
