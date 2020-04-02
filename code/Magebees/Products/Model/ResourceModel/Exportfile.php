<?php
namespace Magebees\Products\Model\ResourceModel;

class Exportfile extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('cws_product_exported_file', 'export_id');
    }
}
