<?php
namespace Magebees\Navigationmenu\Model\ResourceModel;

class Menucreator extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('magebees_menucreator', 'menu_id');
    }
}
