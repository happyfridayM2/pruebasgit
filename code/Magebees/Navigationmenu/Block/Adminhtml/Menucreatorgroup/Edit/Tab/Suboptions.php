<?php
namespace  Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup\Edit\Tab;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;


class Suboptions extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{	
	protected $_yesno;
	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
		\Magebees\Navigationmenu\Helper\Data $helper,
        array $data = array()
    ) {
        $this->_yesno = $yesno;
		$this->_helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    protected function _prepareForm()
    {
		
        $model = $this->_coreRegistry->registry('menucreatorgroup');
		
		$title=$model->getTitle();
		$isElementDisabled = false;
      
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');
		/* Get object of helper using object manager*/ 
		
		$fieldset = $form->addFieldset('megaitem_form_color', array('legend' =>__('First Level Items')));
		
		$fieldset->addField(
            'sublvl1color',
            'text',
            array(
                'name' => 'sub[sublvl1color]',
                'label' => __('Font Color')
				
            )
        );
		$fieldset->addField(
            'sublvl1size',
            'text',
            array(
                'name' => 'sub[sublvl1size]',
                'label' => __('Font Size'),
            )
        );
		$fieldset->addField(
            'sublvl1weight',
            'text',
            array(
                'name' => 'sub[sublvl1weight]',
                'label' => __('Font Weight'),
				
            )
        );
		$fieldset->addField(
            'sublvl1case',
            'select',
            array(
                'name' => 'sub[sublvl1case]',
                'label' => __('Font Transform'),
				'values' =>$this->_helper->getFontTransform()
            )
        );
		
		$fieldset->addField(
            'sublvl1bgcolor',
            'text',
            array(
                'name' => 'sub[sublvl1bgcolor]',
                'label' => __('Background Color')
				
            )
        );
		$fieldset->addField(
            'sublvl1padding',
            'text',
            array(
                'name' => 'sub[sublvl1padding]',
                'label' => __('Padding'),
				'note' => 'Ex: 5px 10px 5px 10px (Top Right Bottom Left)',
            )
        );
		 $subsdividershow = $fieldset->addField(
            'sublvl1showdivider',
            'select',
            array(
                'name' => 'sub[sublvl1showdivider]',
                'label' => __('Show Divider'),
                'title' => __('Show Divider'),
                'values' =>$this->_yesno->toOptionArray(),
            )
        );
		
		$subdividercolor = $fieldset->addField(
            'sublvl1dvcolor',
            'text',
            array(
                'name' => 'sub[sublvl1dvcolor]',
                'label' => __('Divider Color')
                
            )
        );
		$fieldset = $form->addFieldset('mgfrstitem_hover', array('legend' =>__('First Level Items on Hover')));
		$fieldset->addField(
            'sublvl1colorh',
            'text',
            array(
                'name' => 'sub[sublvl1colorh]',
                'label' => __('Font Color')
				
            )
        );
		$fieldset->addField(
            'sublvl1bgcolorh',
            'text',
            array(
                'name' => 'sub[sublvl1bgcolorh]',
                'label' => __('Background Color')
				
            )
        );
		$fieldset = $form->addFieldset('mgfrstitem_active', array('legend' =>__('First Level Items on Active')));
		$fieldset->addField(
            'sublvl1colora',
            'text',
            array(
                'name' => 'sub[sublvl1colora]',
                'label' => __('Font Color')
				
            )
        );
		$fieldset->addField(
            'sublvl1bgcolora',
            'text',
            array(
                'name' => 'sub[sublvl1bgcolora]',
                'label' => __('Background Color')


            )
        );
		$fieldset = $form->addFieldset('megaseconditem_form_color', array('legend' =>__('Second Level Items')));
		$fieldset->addField(
            'sublvl2color',
            'text',
            array(
                'name' => 'sub[sublvl2color]',
                'label' => __('Font Color')
				
            )
        );
		$fieldset->addField(
            'sublvl2size',
            'text',
            array(
                'name' => 'sub[sublvl2size]',
                'label' => __('Font Size'),
            )
        );
		$fieldset->addField(
            'sublvl2weight',
            'text',
            array(
                'name' => 'sub[sublvl2weight]',
                'label' => __('Font Weight'),
				
            )
        );
		$fieldset->addField(
            'sublvl2case',
            'select',
            array(
                'name' => 'sub[sublvl2case]',
                'label' => __('Font Transform'),
				'values' =>$this->_helper->getFontTransform()
            )
        );
		$fieldset->addField(
            'sublvl2bgcolor',
            'text',
            array(
                'name' => 'sub[sublvl2bgcolor]',
                'label' => __('Background Color')
				
            )
        );
		$fieldset->addField(
            'sublvl2padding',
            'text',
            array(
                'name' => 'sub[sublvl2padding]',
                'label' => __('Padding'),
				'note' => 'Ex: 5px 10px 5px 10px (Top Right Bottom Left)',
            )
        );
		$fieldset->addField(
            'sublvl2dvcolor',
            'text',
            array(
                'name' => 'sub[sublvl2dvcolor]',
                'label' => __('Divider Color')
				
            )
        );
		$fieldset = $form->addFieldset('mgsecitem_hover', array('legend' =>__('Second Level Items on Hover')));
		$fieldset->addField(
            'sublvl2colorh',
            'text',
            array(
                'name' => 'sub[sublvl2colorh]',
                'label' => __('Font Color')
				
            )
        );
		$fieldset->addField(
            'sublvl2bgcolorh',
            'text',
            array(
                'name' => 'sub[sublvl2bgcolorh]',
                'label' => __('Background Color')
				
            )
        );
		$fieldset = $form->addFieldset('mgsecitem_active', array('legend' =>__('Second Level Items on Active')));
		$fieldset->addField(
            'sublvl2colora',
            'text',
            array(
                'name' => 'sub[sublvl2colora]',
                'label' => __('Font Color')
				
            )
        );
		$fieldset->addField(
            'sublvl2bgcolora',
            'text',
            array(
                'name' => 'sub[sublvl2bgcolora]',
                'label' => __('Background Color')
				
            )
        );
		
		$fieldset = $form->addFieldset('megathirditem_form_color', array('legend' =>__('Third Level Items')));
		$fieldset->addField(
            'sublvl3color',
            'text',
            array(
                'name' => 'sub[sublvl3color]',
                'label' => __('Font Color')
				
            )
        );
		$fieldset->addField(
            'sublvl3size',
            'text',
            array(
                'name' => 'sub[sublvl3size]',
                'label' => __('Font Size'),
            )
        );
		$fieldset->addField(
            'sublvl3weight',
            'text',
            array(
                'name' => 'sub[sublvl3weight]',
                'label' => __('Font Weight'),
				
            )
        );
		$fieldset->addField(
            'sublvl3case',
            'select',
            array(
                'name' => 'sub[sublvl3case]',
                'label' => __('Font Transform'),
				'values' =>$this->_helper->getFontTransform()
            )
        );
		$fieldset->addField(
            'sublvl3bgcolor',
            'text',
            array(
                'name' => 'sub[sublvl3bgcolor]',
                'label' => __('Background Color')
				
            )
        );
		$fieldset->addField(
            'sublvl3padding',
            'text',
            array(
                'name' => 'sub[sublvl3padding]',
                'label' => __('Padding'),
				'note' => 'Ex: 5px 10px 5px 10px (Top Right Bottom Left)',
            )
        );
		$fieldset->addField(
            'sublvl3dvcolor',
            'text',
            array(
                'name' => 'sub[sublvl3dvcolor]',
                'label' => __('Divider Color')
				
            )
        );
		$fieldset = $form->addFieldset('mgthirditem_hover', array('legend' =>__('Third Level Items on Hover')));
		$fieldset->addField(
            'sublvl3colorh',
            'text',
            array(
                'name' => 'sub[sublvl3colorh]',
                'label' => __('Font Color')
				
            )
        );
		$fieldset->addField(
            'sublvl3bgcolorh',
            'text',
            array(
                'name' => 'sub[sublvl3bgcolorh]',
                'label' => __('Background Color')
				
            )
        );
		$fieldset = $form->addFieldset('mgthirditem_active', array('legend' =>__('Third Level Items on Active')));
		$fieldset->addField(
            'sublvl3colora',
            'text',
            array(
                'name' => 'sub[sublvl3colora]',
                'label' => __('Font Color')
				
            )
        );
		$fieldset->addField(
            'sublvl3bgcolora',
            'text',
            array(
                'name' => 'sub[sublvl3bgcolora]',
                'label' => __('Background Color')
				
            )
        );
		 $this->setChild('form_after', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap($subsdividershow->getHtmlId(), $subsdividershow->getName())
			->addFieldMap($subdividercolor->getHtmlId(), $subdividercolor->getName())
			->addFieldDependence(
                $subdividercolor->getName(),
				$subsdividershow->getName(),
				'1'
            )
			
        );
		
		 
	  $id = $this->getRequest()->getParam('id');

	  if((empty($data)) && ($id == '')) {
			/* First Level Items Options*/
		$data['sublvl1color']='#122736';
		$data['sublvl1size']='15px';
		$data['sublvl1weight']='400';
		$data['sublvl1case']='capitalize';
		$data['sublvl1bgcolor']='#E8E8E8';
		$data['sublvl1padding']='8px 8px 8px 20px';
		$data['sublvl1showdivider']='1';
		$data['sublvl1dvcolor']='#DCDCDC';
		/* First Level Items on Hover  Options*/
		$data['sublvl1colorh']='#FFFFFF';
		$data['sublvl1bgcolorh']='#FE5656';
		/* First Level Items on Active  Options*/
		$data['sublvl1colora']='#FE5656';
		$data['sublvl1bgcolora']='#E8E8E8';
			/* Second Level Items Options*/
		$data['sublvl2color']='#122736';
		$data['sublvl2size']='14px';
		$data['sublvl2weight']='400';
		$data['sublvl2case']='inherit';
		$data['sublvl2bgcolor']='#E0E0E0';
		$data['sublvl2padding']='7px 8px 7px 25px';
		$data['sublvl2dvcolor']='#D5D5D5';
		/* Second Level Items on Hover  Options*/
		$data['sublvl2colorh']='#FFFFFF';
		$data['sublvl2bgcolorh']='#FE5656';
		/* Second Level Items on Active  Options*/
		$data['sublvl2colora']='#FE5656';
		$data['sublvl2bgcolora']='#E0E0E0';
			/* Third Level Items Options*/
		$data['sublvl3color']='#122736';
		$data['sublvl3size']='14px';
		$data['sublvl3weight']='400';
		$data['sublvl3case']='capitalize';
		$data['sublvl3bgcolor']='#D9D9D9';
		$data['sublvl3padding']='6px 8px 6px 30px';
		$data['sublvl3dvcolor']='#CCCCCC';
		/* Third Level Items on Hover  Options*/
		$data['sublvl3colorh']='#FFFFFF';
		$data['sublvl3bgcolorh']='#FE5656';
		/* Third Level Items on Active  Options*/
		$data['sublvl3colora']='#FE5656';
		$data['sublvl3bgcolora']='#D9D9D9';
	  } 
		
		
		
		
		
		if($id)
		{
      
		$informations = $model->getData();
			if(!empty($informations)){
				
			foreach($informations as $key => $value):
				if($this->isJSON($value)){
					$sub_information = json_decode($value, true);
					foreach($sub_information as $subkey => $subvalue):
							$informations[$subkey] = $subvalue;	
						endforeach;
				}else{
					$informations[$key] = $value;	
				}
			endforeach;
			$form->setValues($informations);
			}
			
			

			
		}
		else
		{
			$form->setValues($data);
		}
		
		 $sublvl1color = $form->getElement('sublvl1color');
        $sublvl1colorvalue =  $sublvl1color->getData('value');
        $sublvl1color->setAfterElementHtml($this->getColorPicker($sublvl1color,$sublvl1colorvalue));
		
		$sublvl1bgcolor = $form->getElement('sublvl1bgcolor');
        $sublvl1bgcolorvalue = $sublvl1bgcolor->getData('value');
        $sublvl1bgcolor->setAfterElementHtml($this->getColorPicker($sublvl1bgcolor,$sublvl1bgcolorvalue));
		
		
		
		$sublvl1colorh = $form->getElement('sublvl1colorh');
        $sublvl1colorhvalue = $sublvl1colorh->getData('value');
        $sublvl1colorh->setAfterElementHtml($this->getColorPicker($sublvl1colorh,$sublvl1colorhvalue));
		
		 $sublvl1bgcolorh = $form->getElement('sublvl1bgcolorh');
        $sublvl1bgcolorhvalue = $sublvl1bgcolorh->getData('value');
        $sublvl1bgcolorh->setAfterElementHtml($this->getColorPicker($sublvl1bgcolorh,$sublvl1bgcolorhvalue));
		
		 $sublvl1colora = $form->getElement('sublvl1colora');
        $sublvl1coloravalue =  $sublvl1colora->getData('value');
        $sublvl1colora->setAfterElementHtml($this->getColorPicker($sublvl1colora,$sublvl1coloravalue));
		
		$sublvl1bgcolora = $form->getElement('sublvl1bgcolora');
        $sublvl1bgcoloravalue = $sublvl1bgcolora->getData('value');
        $sublvl1bgcolora->setAfterElementHtml($this->getColorPicker($sublvl1bgcolora,$sublvl1bgcoloravalue));
			
		
		$sublvl2color = $form->getElement('sublvl2color');
        $sublvl2colorvalue = $sublvl2color->getData('value');
        $sublvl2color->setAfterElementHtml($this->getColorPicker($sublvl2color,$sublvl2colorvalue));
		
		$sublvl2bgcolor = $form->getElement('sublvl2bgcolor');
        $sublvl2bgcolorvalue = $sublvl2bgcolor->getData('value');
        $sublvl2bgcolor->setAfterElementHtml($this->getColorPicker($sublvl2bgcolor,$sublvl2bgcolorvalue));
		
		
		
		$sublvl2colorh = $form->getElement('sublvl2colorh');
        $sublvl2colorhvalue = $sublvl2colorh->getData('value');
        $sublvl2colorh->setAfterElementHtml($this->getColorPicker($sublvl2colorh,$sublvl2colorhvalue));
		
		 $sublvl2bgcolorh = $form->getElement('sublvl2bgcolorh');
        $sublvl2bgcolorhvalue = $sublvl2bgcolorh->getData('value');
        $sublvl2bgcolorh->setAfterElementHtml($this->getColorPicker($sublvl2bgcolorh,$sublvl2bgcolorhvalue));
		
		 $sublvl2colora = $form->getElement('sublvl2colora');
        $sublvl2coloravalue = $sublvl2colora->getData('value');
        $sublvl2colora->setAfterElementHtml($this->getColorPicker($sublvl2colora,$sublvl2coloravalue));
		
		$sublvl3bgcolora = $form->getElement('sublvl3bgcolora');
        $sublvl3bgcoloravalue = $sublvl3bgcolora->getData('value');
        $sublvl3bgcolora->setAfterElementHtml($this->getColorPicker($sublvl3bgcolora,$sublvl3bgcoloravalue));
		
		$sublvl3color = $form->getElement('sublvl3color');
        $sublvl3colorvalue = $sublvl3color->getData('value');
        $sublvl3color->setAfterElementHtml($this->getColorPicker($sublvl3color,$sublvl3colorvalue));
		
		$sublvl3bgcolor = $form->getElement('sublvl3bgcolor');
        $sublvl3bgcolorvalue = $sublvl3bgcolor->getData('value');
        $sublvl3bgcolor->setAfterElementHtml($this->getColorPicker($sublvl3bgcolor,$sublvl3bgcolorvalue));
		
		
		$sublvl3colorh = $form->getElement('sublvl3colorh');
        $sublvl3colorhvalue = $sublvl3colorh->getData('value');
        $sublvl3colorh->setAfterElementHtml($this->getColorPicker($sublvl3colorh,$sublvl3colorhvalue));
		
		 $sublvl3bgcolorh = $form->getElement('sublvl3bgcolorh');
        $sublvl3bgcolorhvalue = $sublvl3bgcolorh->getData('value');
        $sublvl3bgcolorh->setAfterElementHtml($this->getColorPicker($sublvl3bgcolorh,$sublvl3bgcolorhvalue));
		
		 $sublvl3colora = $form->getElement('sublvl3colora');
        $sublvl3coloravalue = $sublvl3colora->getData('value');
        $sublvl3colora->setAfterElementHtml($this->getColorPicker($sublvl3colora,$sublvl3coloravalue));
		
		
		
		 $this->setForm($form);
		
	
       
		return parent::_prepareForm();
    }
	public function getColorPicker($element,$value){
        return '<script type="text/javascript">
            require(["jquery","jquery/colorpicker/js/colorpicker"], function ($) {
                $(document).ready(function () {
                    var $el = $("#' . $element->getHtmlId() . '");
                    $el.css("backgroundColor", "'. $value .'");

                    // Attach the color picker
                    $el.ColorPicker({
                        color: "'. $value .'",
                        onChange: function (hsb, hex, rgb) {
                            $el.css("backgroundColor", "#" + hex).val("#" + hex);
                        }
                    });
                });
            });
            </script>';
    }


	
	 public function getTabLabel()
    {
        return __('Menu Group');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Menu Group');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
	public function isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
   
 }

 
