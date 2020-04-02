<?php
namespace Magebees\Navigationmenu\Model\ResourceModel\Menucreatorgroup;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magebees\Navigationmenu\Model\Menucreatorgroup', 'Magebees\Navigationmenu\Model\ResourceModel\Menucreatorgroup');
    }
    public function toOptionArray()
    {
		$result = array();
		$options = array(array('value' => '', 'label' => 'Please Select'));
        $result = array_merge($options,parent::_toOptionArray('unique_code', 'title'));
		return $result;
   }
}
