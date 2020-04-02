<?php
namespace Magebees\Products\Model;

class Validationlog extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
		$this->_init('Magebees\Products\Model\ResourceModel\Validationlog');
    }
}