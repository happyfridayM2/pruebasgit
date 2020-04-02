<?php
namespace Magebees\Products\Model\ResourceModel\Exportfile;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected function _construct()
	{
		$this->_init('Magebees\Products\Model\Exportfile', 'Magebees\Products\Model\ResourceModel\Exportfile');
	}
}