<?php
namespace Magebees\Navigationmenu\Model;

class Customer extends \Magento\Framework\Model\AbstractModel
{
	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $session,
		array $data = []
    ) {
        $this->_session = $session;
		parent::_construct();
    }
    public function isLoggedIn()
    {
        $id=$this->_session->getCustomerId();
        if ($this->_session->isLoggedIn()) {
            return 1;
        } else {
            return 0;
        }
    }
    public function getUserPermission($customerStatus=null,$customerGroupId=null)
    {
		
		$permission = [];
        $permission [] = -2; /* For Public Menu Items*/
        $customerGroup = null;
		if(($customerGroupId) && ($customerStatus))
		{
			if($customerStatus=='login')
			{
				$permission [] = -1;/* For Register User Menu Items*/
				$permission [] = $customerGroupId;
			}else{
				$permission [] =$customerGroupId;
			}
		}else{
				if ($this->_session->isLoggedIn()) {
					$customerGroup =$this->_session->getCustomerGroupId();
					$permission [] = -1;/* For Register User Menu Items*/
					$permission [] = $customerGroup;
				} else {
					$permission [] =$this->_session->getCustomerGroupId();
				}
			
		}
		return $permission;
    }
}
