<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    private $_profileCollection=null;
    private $_state=null;

    /**
     * UpgradeData constructor.
     * @param \Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\App\State $state
    )
    {
        $this->_profileCollection=$profileCollectionFactory->create();
        $this->_state=$state;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        $installer=$setup;
        $installer->startSetup();

        /**
         * upgrade to 7.0.0
         */
        if (version_compare($context->getVersion(), '7.0.0') < 0) {
            try {
                $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            } catch (\Exception $e) {

            }
            foreach ($this->_profileCollection as $profile) {
                $pattern=str_replace(["php="], ["output="], $profile->getBody());
                $profile->setBody($pattern);
                $profile->save();
            }
        }

        /**
         * upgrade to 8.0.0
         */
        if (version_compare($context->getVersion(), '8.0.0') < 0) {

            try {
                $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            } catch (\Exception $e) {

            }
            foreach ($this->_profileCollection as $profile) {
                $pattern=preg_replace("#(\"|')(\{\{[a-zA-Z]+\.[a-zA-Z_0-9]+\}\})\\1#m", "$2", $profile->getBody());
                $profile->setBody($pattern);
                $profile->save();

            }



            $template=array(

                "name"=>"ERP/CRM export/import",
                "type"=>"2",
                "encoding"=>"UTF-8",
                "path"=>"/pub/export/",
                "product_type"=>"simple,configurable,grouped_parent,bundle_parent,bundle_children",
                "store_id"=>"1",
                "single_export"=>"0",
                "date_format"=>"{f}",
                "product_relation"=>"all",
                "repeat_for_each"=>"1",
                "repeat_for_each_increment"=>"2",
                "extra_header"=>">>>> START IMPORT",
                "body"=>"## NEW ORDER
ORDER ID = {{order.increment_id}}
CUSTOMER = {{order.customer_lastname}} {{order.customer_firstname}}
 
<?php foreach(\$products as \$product): ?>
        PRODUCT={{product.sku}} {{product.qty_ordered}} {{product.base_row_total output=\"number_format(\$self,2)\"}}$      
<?php endforeach; ?>
BILLING =    
		{{shipping.firstname}} {{shipping.lastname}} 
        {{shipping.postcode}} {{shipping.city}} 
		{{shipping.street output=\"group(\$self,'|')\"}}
		{{shipping.country_id}}
SHIPPING LABEL =   
        {{shipping.firstname}} {{shipping.lastname}} 
        {{shipping.postcode}} {{shipping.city}} 
		line 1: {{shipping.street output=\"split(\$self,1)\"}}
		line 2: {{shipping.street output=\"split(\$self,2)\"}}
		line 3: {{shipping.street output=\"split(\$self,3)\"}}
		line 4: {{shipping.street output=\"split(\$self,4)\"}}
		{{shipping.country_id}}

##",
                "attributes"=>"{\"0\":{\"checked\":false,\"code\":\"order.adjustment_negative\",\"condition\":\"eq\",\"value\":\"\"},\"1\":{\"checked\":false,\"code\":\"order.adjustment_negative\",\"condition\":\"eq\",\"value\":\"\"},\"2\":{\"checked\":false,\"code\":\"order.adjustment_negative\",\"condition\":\"eq\",\"value\":\"\"},\"3\":{\"checked\":false,\"code\":\"order.adjustment_negative\",\"condition\":\"eq\",\"value\":\"\"},\"4\":{\"checked\":false,\"code\":\"order.adjustment_negative\",\"condition\":\"eq\",\"value\":\"\"},\"5\":{\"checked\":false,\"code\":\"order.adjustment_negative\",\"condition\":\"eq\",\"value\":\"\"},\"6\":{\"checked\":false,\"code\":\"order.adjustment_negative\",\"condition\":\"eq\",\"value\":\"\"},\"7\":{\"checked\":false,\"code\":\"order.adjustment_negative\",\"condition\":\"eq\",\"value\":\"\"},\"8\":{\"checked\":false,\"code\":\"order.adjustment_negative\",\"condition\":\"eq\",\"value\":\"\"},\"9\":{\"checked\":false,\"code\":\"order.adjustment_negative\",\"condition\":\"eq\",\"value\":\"\"},\"10\":{\"checked\":false,\"code\":\"order.adjustment_negative\",\"condition\":\"eq\",\"value\":\"\"}}",
                "states"=>"canceled,closed,complete,payment_review,fraud,processing,fraud,holded,pending,pending_payment",
                "customer_groups"=>"0,1,2,3",
                "scheduled_task"=>"{\"days\": [\"Wednesday\", \"Thursday\"], \"hours\": [\"03:00\", \"04:00\", \"05:00\", \"06:00\"]}",
                "storage_enabled"=>"1",
                "extra_footer"=>">>>> END IMPORT ",
                "format"=>"2",
                "scope"=>"order",
            );

            $installer->getConnection()->insert($installer->getTable('ordersexporttool_profiles'), $template);


            $functions=array(

                array(
                    "script"=>"<?php

function split(\$self,\$nth=1){

 \$parts= explode(\"\\n\",\$self);
  if(isset(\$parts[\$nth-1])){
  	return trim(\$parts[\$nth-1]);
  }
  return null;
}

?>"
                ),
                array(
                    "script"=>"<?php
function group(\$self,\$glue=\", \"){
   \$parts= explode(\"\\n\",\$self); 
   foreach(\$parts as \$key => \$value){
   	\$parts[\$key]=trim(\$value);
   }
   return implode(\$glue,\$parts); 
 } 
?>"
                )
            );

            foreach ($functions as $function) {
                $installer->getConnection()->insert($installer->getTable('ordersexporttool_functions'), $function);
            }
        }
        $installer->endSetup();
    }
}