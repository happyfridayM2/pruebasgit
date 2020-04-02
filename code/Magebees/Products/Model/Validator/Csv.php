<?php
namespace Magebees\Products\Model\Validator;

class Csv extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $fields;
    protected $mapfields = array();
	
	protected function _construct()
	{
	  
	}
	public function associateDataWithHeader($header,$data)
	{
		if(count($header)==count($data))
		{
			$master_data=array();
			$i=0;
			foreach($data as $d)
			{
				$master_data[$header[$i]]=$d;
				$i++;
			}
			return $master_data;			
		}
	}
    public function parse($importfiletype,$validationBehavior,$files,$pointer_req)
    {
		$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resolver = $_objectManager->get('Magento\Framework\Locale\Resolver');
        setlocale(LC_ALL, $resolver->getLocale().'.UTF-8');
		$vardir = \Magento\Framework\App\ObjectManager::getInstance();
		$filesystem = $vardir->get('Magento\Framework\Filesystem');
		$reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
		$file = $reader->getAbsolutePath("import/".urldecode($files));
		$handle = fopen($file,'r');
		$pointer='';
		$master_data=array();
		$header=true;
		$header_data=array();
		$row=0;
		while ( ($data = fgetcsv($handle) ) !== FALSE ) {
			if($header){
				$header=false;
				$header_data=$data;
				if($pointer_req!=''){
					fseek($handle,$pointer_req);				
				}
				continue;		
			}else{
				if(array_filter($data)) {
					$t_master_data=$this->associateDataWithHeader($header_data,$data);				
					$t_master_data['cws_row_header']=$row;
					array_push($master_data,$t_master_data);				
				}
			}
			if($row==10000)
			{
				$pointer=ftell($handle);
				break;
			}
			$row++;
		}
		$url='';
		if($pointer!=''){
			$obj = $_objectManager->get('Magento\Framework\UrlInterface');
			$url = $obj->getUrl('products/import/validate',array('pointer'=>$pointer,'files'=>$files,'importfiletype'=>$importfiletype,'validationBehavior'=>$validationBehavior));
		}
		return array("data"=>$master_data,'url'=>$url);
	}
	public function unparse()
	{

	}
}