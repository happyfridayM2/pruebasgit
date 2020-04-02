<?php
namespace Magebees\Products\Block\Adminhtml\Export\Edit\Tab;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class Exportfile extends Extended
{
	protected $_exportfile;
 	public function __construct(
 			\Magento\Backend\Block\Template\Context $context,
 			\Magebees\Products\Model\Exportfile $exportfile,
 			\Magento\Framework\Registry $coreRegistry,
 			array $data = []
 	) {

		$this->_exportfile = $exportfile;
 		parent::__construct($context, $backendHelper, $data);
 	}
 	protected function _construct()
  	{
  		parent::_construct();
 		$this->setId('slidergroup_product_section');
 		$this->setDefaultSort('entity_id');
		$this->setUseAjax(true);
		$this->setDefaultFilter(['in_products' => 1]);
 	}
	public function getSelectedProducts()
    {
        $products = $this->getProductsSlider();
		$model = $this->_coreRegistry->registry('slidergroup_data');
			$id = $this->getRequest()->getParam('id');
			if($id) {
				if($id)	{
					$id = $id;
				}else{
					$id = $model->getId();
				}
				$product_model = $this->_prdoctdata->getCollection()
					->addFieldToFilter('slidergroup_id',array('eq' => $id));
				$Product_val = array();
				foreach($product_model as $product_data){
					$Product_val[] = $product_data->getData('product_sku');
				}
			}
		if($id){
			if(!empty($products)){
				$products = array_merge($Product_val,$products);
				return $products;
			} else{
				$products = $Product_val;
				return $products;
			}
		}else{
			return $products;
		}
	}
	protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $productIds = $this->getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
 	protected function _prepareCollection()
 	{
	    $collection = $this->_exportfile->create()->getCollection();
 		$this->setCollection($collection);
 		return parent::_prepareCollection();
 	}
 	public function _prepareColumns()
 	{
 		$this->addColumn(
                'in_products',
                [
                    'type' => 'checkbox',
                    'name' => 'in_products',
                    'values' => $this->getSelectedProducts(),
                    'align' => 'center',
                    'index' => 'entity_id',
                    'header_css_class' => 'col-select',
                    'column_css_class' => 'col-select'
                ]
            );
 		$this->addColumn(
            'export_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'export_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );	
 		$this->addColumn(
 				'file_name',
 				[
 						'header' => __('File Name'),
 						'index' => 'file_name',
 						'header_css_class' => 'col-name',
 						'column_css_class' => 'col-name'
 				]
 		);
 		return parent::_prepareColumns();
 	}
	
	public function getGridUrl()
	{
		return $this->getUrl('*/*/productgrids', ['_current' => true]);
	}
 }