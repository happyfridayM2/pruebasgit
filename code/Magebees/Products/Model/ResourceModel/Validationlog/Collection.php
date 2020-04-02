<?php
namespace Magebees\Products\Model\ResourceModel\Validationlog;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected function _construct()
	{
		$this->_init('Magebees\Products\Model\Validationlog', 'Magebees\Products\Model\ResourceModel\Validationlog');
	}
	
}