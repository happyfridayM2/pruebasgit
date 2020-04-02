<?php
namespace Magebees\Products\Model\ResourceModel;

class Importlog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('cws_product_import_log', 'log_id');
    }
    public function truncate()
    {
        $this->_getConnection('write')->query('TRUNCATE TABLE '.$this->getMainTable());
        return $this;
    }
}
