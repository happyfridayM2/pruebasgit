<?php
namespace Magebees\Products\Model;

class Profiler extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
		$this->_init('Magebees\Products\Model\ResourceModel\Profiler');
    }
}