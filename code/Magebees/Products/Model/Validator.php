<?php
namespace Magebees\Products\Model;

class Validator extends \Magento\Framework\Model\AbstractModel
{
	protected $validation_status=false;
	protected $request;
	protected $helper;

	public function __construct(
		\Magento\Framework\App\Request\Http $request,
		\Magebees\Products\Helper\Data $helper
	) {
		$this->request 	= $request;
		$this->helper 	= $helper;
		
	}
	public function setProfilerData()
	{
		$data=$this->getConverter();
		$row=array();
		foreach($data['data'] as $d)
		{
			$temp_row=array();
			$temp_row['product_data'] = $this->helper->getSerializeData($d);
			$temp_row['validate']=$this->validation_status;
			$row[]=$temp_row;				
			if(count($row)>5 || $d === end($data['data']))
			{
				try{
					$model = $this->helper->getObjectManager()->create('\Magebees\Products\Model\ResourceModel\Profiler');
					$model->insertMultipleProduct($row);
				}catch(\Exception $e){
				
				}					
				$row=array();
			}
		}
		return $data['url'];
  	}
	public function getConverter()
	{
		$importfiletype = $this->request->getParam('importfiletype');
		$this->setValidationStatus();
		$validationBehavior = $this->request->getParam('validationBehavior');
		$files = $this->request->getParam('files');
		$pointer = $this->request->getParam('pointer');
		switch($importfiletype){
			default:
				$model = $this->helper->getObjectManager()->create('\Magebees\Products\Model\Validator\Csv');
				return $model->parse($importfiletype,$validationBehavior,$files,$pointer);
				break;
		}
	}
	public function setValidationStatus(){
		$validate = $this->request->getParam('validationBehavior');
		if($validate == 'skip'){
			$this->validation_status=true;
		}
	}
}