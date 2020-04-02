<?php
namespace Magebees\Navigationmenu\Model\ResourceModel;

class Menucreatorgroup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('magebees_menucreatorgroup', 'group_id');
    }
}
