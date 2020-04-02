<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Module data installation
 */
class InstallData implements InstallDataInterface
{
    protected $_productSetupFactory;

    /**
     * @param \Wyomind\OrdersExportTool\Setup\ProductSetupFactory $productSetupFactory
     */
    public function __construct(ProductSetupFactory $productSetupFactory)
    {
        $this->_productSetupFactory = $productSetupFactory;
    }

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface   $context
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        unset($context);
        $productSetup = $this->_productSetupFactory->create(['setup' => $setup]);

        $productSetup->installEntities();

        $installer = $setup;
        $installer->startSetup();

        $data = [];
        $data['templates'] = [];

        $data['templates'][] = [
            'name' => "xml_sample",
            'type' => "1",
            'encoding' => "UTF-8",
            'path' => "/pub/export/",
            'product_type' => "simple,configurable,grouped_parent,bundle_parent,bundle_children",
            'store_id' => "1",
            'flag' => "1",
            'single_export' => "0",
            'updated_at' => "2014-02-10 11:22:07",
            'last_exported_id' => "0",
            'automatically_update_last_order_id' => "0",
            'date_format' => "{f}",
            'include_header' => "0",
            'product_relation' => "all",
            'repeat_for_each_increment' => "0",
            'incremental_column' => "0",
            'header' => "<orders>",
            'body' => "<order id=\"{{order.entity_id}}\" no=\"{{order.increment_id}}\">
    <customer>
    {{order.customer_lastname}} {{order.customer_firstname}}
    </customer>
    <billing>
        {{billing.firstname}} {{billing.lastname}} 
        {{billing.postcode}} {{billing.street}} 
        {{billing.city}} {{billing.country_id}}
    </billing>
    <shipping>
        {{shipping.firstname}} {{shipping.lastname}} 
        {{shipping.postcode}} {{shipping.street}}
        {{shipping.city}} {{shipping.country_id}}
    </shipping>
    <items>
        <?php foreach(\$products as \$product): ?>
        <item id=\"{{product.item_id}}\">{{product.name}}</item>
        <weight>{{product.weight}}</weight>
        <?php endforeach; ?>
    </items>
    <payments>
        <?php foreach(\$payments as \$payment): ?>
        <payment id=\"{{payment.entity_id}}\">
            {{payment.method}}
        </payment>
        <?php endforeach; ?>
    </payments>
    <invoices>
        <?php foreach(\$invoices as \$invoice): ?>
        <invoice id=\"{{invoice.entity_id}}\">
                    {{invoice.base_grand_total}}{{invoice.base_currency_code}}
        </invoice>
        <?php endforeach; ?>
    </invoices>
    <shipments>
        <?php foreach(\$shipments as \$shipment): ?>
        <shipment id=\"{{shipment.entity_id}}\">
                    {{shipment.shipment_status}}
        </shipment>
        <?php endforeach; ?>
    </shipments>
    <creditmemos>
        <?php foreach(\$creditmemos as \$creditmemo): ?>
        <creditmemo id=\"{{creditmemo.entity_id}}\">
            {{payment.amount_canceled}}
        </creditmemo>
        <?php endforeach; ?>
    </creditmemos>
</order>",
            'footer' => "</orders>",
            'separator' => ";",
            'protector' => "",
            'enclose_data' => "1",
            'attributes' => "[]",
            "states"=>"canceled,closed,complete,payment_review,fraud,processing,fraud,holded,pending,pending_payment",
            "customer_groups"=>"0,1,2,3",
            'scheduled_task' => '{"days": ["Wednesday", "Thursday"], "hours": ["03:00", "04:00", "05:00", "06:00"]}',
            'ftp_enabled' => "0",
        ];

        $data['templates'][] = [
            'name' => "txt_sample",
            'type' => "3",
            'encoding' => "UTF-8",
            'path' => "/pub/export/",
            'product_type' => "simple,configurable,grouped_parent,bundle_parent,bundle_children",
            'store_id' => "1",
            'flag' => "1",
            'single_export' => "0",
            'updated_at' => "2014-02-10 11:22:07",
            'last_exported_id' => "0",
            'automatically_update_last_order_id' => "0",
            'date_format' => "{f}",
            'include_header' => "1",
            'product_relation' => "all",
            'repeat_for_each' => "0",
            'order_by' => "0",
            'incremental_column' => "0",
            'header' => '{"header":["body#","customer","Shipping Address","Product name","Product sku","Product price","Total price"]}',
            'body' => '{"body":["{{order.increment_id}}","{{order.customer_firstname}} {{order.customer_lastname}}","{{shipping.firstname}} {{shipping.lastname}} {{shipping.middlename}}{{shipping.city}} {{shipping.postcode}}{{shipping.region}} {{shipping.street}}","{{product.name}}","{{product.sku}}","{{product.price}}","{{invoice.base_grand_total}}"]}',
            'separator' => ";",
            'enclose_data' => "0",
            'attributes' => "[]",
            "states"=>"canceled,closed,complete,payment_review,fraud,processing,fraud,holded,pending,pending_payment",
            "customer_groups"=>"0,1,2,3",
            'scheduled_task' => "{\"days\":[],\"hours\":[]}",
            'ftp_enabled' => "0",
            'ftp_host' => "",
            'ftp_login' => "",
            'ftp_password' => "",
            'ftp_active' => "0",
            'ftp_dir' => "/",
            'use_sftp' => "0"
        ];

        $data['templates'][] = [
            'name' => "YahooStore",
            'type' => "1",
            'encoding' => "UTF-8",
            'path' => "/pub/export/",
            'product_type' => "simple,configurable,grouped_parent,bundle_parent,bundle_children",
            'store_id' => "1,3,2",
            'flag' => "1",
            'single_export' => "1",
            'updated_at' => "2014-02-10 11:24:02",
            'automatically_update_last_order_id' => "1",
            'date_format' => "{f}",
            'include_header' => "0",
            'product_relation' => "all",
            'repeat_for_each' => "0",
            'order_by' => "",
            'order_by_field' => "0",
            'incremental_column_name' => "0",
            'header' => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
http://store.yahoo.com/doc/dtd/OrderList2.dtd
<OrderList StoreAccountName=\"mystorename\">",
            'body' => "<Order currency=\"{{order.base_currency_code}}\" id=\"{{order.increment_id}}\">
  <Time>{{order.created_at}}</Time>
  <NumericTime><?php return strtotime(\"{{order.created_at}}\"); ?></NumericTime>
  <Referer></Referer>
  <Entry-Point></Entry-Point>
  <AddressInfo type=\"ship\">
   <Name>
    <First>{{order.customer_firstname}}</First>
    <Last>{{order.customer_lastname}}</Last>
    <Full>{{order.customer_firstname}} {{order.customer_lastname}}</Full>
   </Name>
   <Company>{{shipping.company}}</Company>
    {{shipping.street}}
   <Address1>{{shipping.street}}</Address1>
   <Address2></Address2>
   <City>{{shipping.city}}</City>
   <State>{{shipping.region}}</State>
   <Country>{{shipping.country_id}}</Country>
   <Zip>{{shipping.postcode}}</Zip>
   <Phone>{{shipping.telephone}}</Phone>
   <Email>{{shipping.email}}</Email>
  </AddressInfo>
  <AddressInfo type=\"bill\">
     <Name>
    <First>{{order.customer_firstname}}</First>
    <Last>{{order.customer_lastname}}</Last>
    <Full>{{order.customer_firstname}} {{order.customer_lastname}}</Full>
   </Name>
   <Company>{{billing.company}}</Company>
   <Address1>{{billing.street}}</Address1>
   <Address2></Address2>
   <City>{{billing.city}}</City>
   <State>{{billing.region}}</State>
   <Country>{{billing.country_id}}</Country>
   <Zip>{{billing.postcode}}</Zip>
   <Phone>{{billing.telephone}}</Phone>
   <Email>{{billing.email}}</Email>
  </AddressInfo>
  <IPAddress>{{order.remote_ip}}</IPAddress>
  <Shipping>{{order.shipping_description}}</Shipping>
 
<?php foreach(\$products as \$product): ?>
  <Item num=\"0\">
   <Id>{{product.product_id}}</Id>
   <Code>{{product.sku}}</Code>
   <Quantity>{{product.qty_ordered}}</Quantity>
   <Unit-Price>{{product.price}}</Unit-Price>
   <Description>{{product.description}}</Description>
   <Url>{{product.url}}</Url>
   <Taxable>YES</Taxable>
   <Thumb></Thumb>
  </Item>
<?php endforeach; ?>
  <Total>
   <Line type=\"Subtotal\" name=\"Subtotal\">{{order.subtotal}}</Line>
   <Line type=\"Shipping\" name=\"Shipping\">{{order.shipping_amount}}</Line>
   <Line type=\"Tax\" name=\"Tax\">{{order.tax_amount}}</Line>
   <Line type=\"Total\" name=\"Total\">{{order.grand_total}}</Line>
  </Total>
 </Order>",
            'footer' => "</OrderList>",
            'separator' => ";",
            'enclose_data' => "0",
            'attributes' => "[]",
            "states"=>"canceled,closed,complete,payment_review,fraud,processing,fraud,holded,pending,pending_payment",
            "customer_groups"=>"0,1,2,3",
            'scheduled_task' => "{\"days\":[],\"hours\":[]}",
            'ftp_enabled' => "0",
        ];


        

        $data['variables'] = [
            [
                'scope' => "product",
                'name' => "description",
                'comment' => "get the description",
                'script' => "<?php\r\n\r\n\$om = \\Magento\\Framework\\App\\ObjectManager::getInstance();\r\n\$model = \$om->get('\\Magento\\Catalog\\Model\\Product');\r\n\$product = \$model->load(\$item->getProductId());\r\nreturn \$product->getDescription();\r\n\r\n?>",
            ],
            [
                'scope' => "product",
                'name' => "url",
                'comment' => "get the url",
                'script' => "<?php\r\n\r\n\$om = \Magento\\Framework\\App\\ObjectManager::getInstance();\r\n\$model = \$om->get('\\Magento\\Catalog\\Model\\Product');\r\n\$product = \$model->load(\$item->getProductId());\r\nreturn \$product->getProductUrl();\r\n\r\n?>",
            ]
        ];

        foreach ($data['templates'] as $template) {
            $installer->getConnection()->insert($installer->getTable('ordersexporttool_profiles'), $template);
        }

        foreach ($data['variables'] as $function) {
            $installer->getConnection()->insert($installer->getTable('ordersexporttool_variables'), $function);
        }

        $installer->endSetup();
    }
}