<?php
namespace  Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class Megamenuitem extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

   
    protected $_yesno;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
		\Magebees\Navigationmenu\Helper\Data $helper,
        array $data = []
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
        
         $fieldset = $form->addFieldset('megaparent_form_color', ['legend' =>__('MegaMenu Panel')]);
        if ($model->getId()) {
            $fieldset->addField('group_id', 'hidden', ['name' => 'group_id']);
        }
        $fieldset->addField(
            'mmpnl-padding',
            'text',
            [
                'name' => 'mega[mmpnl-padding]',
                'label' => __('Padding'),
                'note' => 'Ex: 5px 10px 5px 10px (Top Right Bottom Left)',
            ]
        );
        
          $fieldset->addField(
              'mmpnl-bgcolor',
              'text',
              [
                'name' => 'mega[mmpnl-bgcolor]',
                'label' => __('Background Color')
                
              ]
          );
        
        
        $fieldset->addField(
            'mmpnl-bdwidth',
            'text',
            [
                'name' => 'mega[mmpnl-bdwidth]',
                'label' => __('Border Width'),
                'note' => 'Ex: 5px 10px 5px 10px (Top Right Bottom Left)',
            ]
        );
        $fieldset->addField(
            'mmpnl-bdcolor',
            'text',
            [
                'name' => 'mega[mmpnl-bdcolor]',
                'label' => __('Border Color')
                
            ]
        );
        
        $fieldset->addField(
            'mmpnl-corner',
            'text',
            [
                'name' => 'mega[mmpnl-corner]',
                'label' => __('Mega Menu Rounded Corner'),
                'note' => 'Ex: 5px 10px 5px 10px (Top-left Top-Right Bottom-Right Bottom-Left)',
            ]
        );
        
        $fieldset->addField(
            'mmpnl-clm-padding',
            'text',
            [
                'name' => 'mega[mmpnl-clm-padding]',
                'label' => __('Mega Menu Column Padding'),
                'note' => 'Ex: 5px 10px 5px 10px (Top Right Bottom Left)',
            ]
        );
        
        $fieldset = $form->addFieldset('megaitem_form_color', ['legend' =>__('First Level Items')]);
        
        $fieldset->addField(
            'mmlvl1color',
            'text',
            [
                'name' => 'mega[mmlvl1color]',
                'label' => __('Font Color')
                
            ]
        );
        
        
        
        $fieldset->addField(
            'mmlvl1size',
            'text',
            [
                'name' => 'mega[mmlvl1size]',
                'label' => __('Font Size'),
            ]
        );
        $fieldset->addField(
            'mmlvl1weight',
            'text',
            [
                'name' => 'mega[mmlvl1weight]',
                'label' => __('Font Weight'),
                
            ]
        );
        $fieldset->addField(
            'mmlvl1case',
            'select',
            [
                'name' => 'mega[mmlvl1case]',
                'label' => __('Font Transform'),
                'values' =>$this->_helper->getFontTransform()
            ]
        );
        $fieldset->addField(
            'mmlvl1bgcolor',
            'text',
            [
                'name' => 'mega[mmlvl1bgcolor]',
                'label' => __('Background Color')
                
            ]
        );
        
        
        
        
        $fieldset->addField(
            'mmlvl1padding',
            'text',
            [
                'name' => 'mega[mmlvl1padding]',
                'label' => __('Padding'),
                'note' => 'Ex: 5px 10px 5px 10px (Top Right Bottom Left)',
            ]
        );
        $megalvl1dividershow = $fieldset->addField(
            'mmlvl1showdivider',
            'select',
            [
                'name' => 'mega[mmlvl1showdivider]',
                'label' => __('Show Divider'),
                'title' => __('Show Divider'),
                'values' =>$this->_yesno->toOptionArray(),
            ]
        );
        
        $megalvl1dividercolor = $fieldset->addField(
            'mmlvl1dvcolor',
            'text',
            [
                'name' => 'mega[mmlvl1dvcolor]',
                'label' => __('Divider Color')
                
            ]
        );
        
        
        $fieldset = $form->addFieldset('mgfrstitem_hover', ['legend' =>__('First Level Items on Hover')]);
        $fieldset->addField(
            'mmlvl1colorh',
            'text',
            [
                'name' => 'mega[mmlvl1colorh]',
                'label' => __('Font Color')
                
            ]
        );
        $fieldset->addField(
            'mmlvl1bgcolorh',
            'text',
            [
                'name' => 'mega[mmlvl1bgcolorh]',
                'label' => __('Background Color')
                
            ]
        );
        
        
    
        $fieldset = $form->addFieldset('mgfrstitem_active', ['legend' =>__('First Level Items on Active')]);
        $fieldset->addField(
            'mmlvl1colora',
            'text',
            [
                'name' => 'mega[mmlvl1colora]',
                'label' => __('Font Color')
                
            ]
        );
        $fieldset->addField(
            'mmlvl1bgcolora',
            'text',
            [
                'name' => 'mega[mmlvl1bgcolora]',
                'label' => __('Background Color')
                
            ]
        );
        $fieldset = $form->addFieldset('megaseconditem_form_color', ['legend' =>__('Second Level Items')]);
        $fieldset->addField(
            'mmlvl2color',
            'text',
            [
                'name' => 'mega[mmlvl2color]',
                'label' => __('Font Color')
                
            ]
        );
        $fieldset->addField(
            'mmlvl2size',
            'text',
            [
                'name' => 'mega[mmlvl2size]',
                'label' => __('Font Size'),
            ]
        );
        $fieldset->addField(
            'mmlvl2weight',
            'text',
            [
                'name' => 'mega[mmlvl2weight]',
                'label' => __('Font Weight'),
                
            ]
        );
        $fieldset->addField(
            'mmlvl2case',
            'select',
            [
                'name' => 'mega[mmlvl2case]',
                'label' => __('Font Transform'),
                'values' =>$this->_helper->getFontTransform()
            ]
        );
        $fieldset->addField(
            'mmlvl2bgcolor',
            'text',
            [
                'name' => 'mega[mmlvl2bgcolor]',
                'label' => __('Background Color'),
                'class' => 'color {required:false}'
            ]
        );
        $fieldset->addField(
            'mmlvl2padding',
            'text',
            [
                'name' => 'mega[mmlvl2padding]',
                'label' => __('Padding'),
                'note' => 'Ex: 5px 10px 5px 10px (Top Right Bottom Left)',
            ]
        );
        
        
        
         $megalvl2dividershow = $fieldset->addField(
             'mmlvl2showdivider',
             'select',
             [
                'name' => 'mega[mmlvl2showdivider]',
                'label' => __('Show Divider'),
                'title' => __('Show Divider'),
                'values' =>$this->_yesno->toOptionArray(),
             ]
         );
        
        $megalvl2dividercolor = $fieldset->addField(
            'mmlvl2dvcolor',
            'text',
            [
                'name' => 'mega[mmlvl2dvcolor]',
                'label' => __('Divider Color')
                
            ]
        );
        
        $fieldset = $form->addFieldset('mgsecitem_hover', ['legend' =>__('Second Level Items on Hover')]);
        $fieldset->addField(
            'mmlvl2colorh',
            'text',
            [
                'name' => 'mega[mmlvl2colorh]',
                'label' => __('Font Color')
                
            ]
        );
        $fieldset->addField(
            'mmlvl2bgcolorh',
            'text',
            [
                'name' => 'mega[mmlvl2bgcolorh]',
                'label' => __('Background Color')
                
            ]
        );
        $fieldset = $form->addFieldset('mgsecitem_active', ['legend' =>__('Second Level Items on Active')]);
        $fieldset->addField(
            'mmlvl2colora',
            'text',
            [
                'name' => 'mega[mmlvl2colora]',
                'label' => __('Font Color')
                
            ]
        );
        $fieldset->addField(
            'mmlvl2bgcolora',
            'text',
            [
                'name' => 'mega[mmlvl2bgcolora]',
                'label' => __('Background Color')
                
            ]
        );
        
        $fieldset = $form->addFieldset('megathirditem_form_color', ['legend' =>__('Third Level Items')]);
        $fieldset->addField(
            'mmlvl3color',
            'text',
            [
                'name' => 'mega[mmlvl3color]',
                'label' => __('Font Color')
                
            ]
        );
        $fieldset->addField(
            'mmlvl3size',
            'text',
            [
                'name' => 'mega[mmlvl3size]',
                'label' => __('Font Size'),
            ]
        );
        $fieldset->addField(
            'mmlvl3weight',
            'text',
            [
                'name' => 'mega[mmlvl3weight]',
                'label' => __('Font Weight'),
                
            ]
        );
        $fieldset->addField(
            'mmlvl3case',
            'select',
            [
                'name' => 'mega[mmlvl3case]',
                'label' => __('Font Transform'),
                'values' =>$this->_helper->getFontTransform()
            ]
        );
        $fieldset->addField(
            'mmlvl3bgcolor',
            'text',
            [
                'name' => 'mega[mmlvl3bgcolor]',
                'label' => __('Background Color')
                
            ]
        );
        $fieldset->addField(
            'mmlvl3padding',
            'text',
            [
                'name' => 'mega[mmlvl3padding]',
                'label' => __('Padding'),
                'note' => 'Ex: 5px 10px 5px 10px (Top Right Bottom Left)',
            ]
        );
        
         $megalvl3dividershow = $fieldset->addField(
             'mmlvl3showdivider',
             'select',
             [
                'name' => 'mega[mmlvl3showdivider]',
                'label' => __('Show Divider'),
                'title' => __('Show Divider'),
                'values' =>$this->_yesno->toOptionArray(),
             ]
         );
        
        $megalvl3dividercolor = $fieldset->addField(
            'mmlvl3dvcolor',
            'text',
            [
                'name' => 'mega[mmlvl3dvcolor]',
                'label' => __('Divider Color')
                
            ]
        );
        
        
        $fieldset = $form->addFieldset('mgthirditem_hover', ['legend' =>__('Third Level Items on Hover')]);
        $fieldset->addField(
            'mmlvl3colorh',
            'text',
            [
                'name' => 'mega[mmlvl3colorh]',
                'label' => __('Font Color')
                
            ]
        );
        $fieldset->addField(
            'mmlvl3bgcolorh',
            'text',
            [
                'name' => 'mega[mmlvl3bgcolorh]',
                'label' => __('Background Color')
                
            ]
        );
        $fieldset = $form->addFieldset('mgthirditem_active', ['legend' =>__('Third Level Items on Active')]);
        $fieldset->addField(
            'mmlvl3colora',
            'text',
            [
                'name' => 'mega[mmlvl3colora]',
                'label' => __('Font Color')
                
            ]
        );
        $fieldset->addField(
            'mmlvl3bgcolora',
            'text',
            [
                'name' => 'mega[mmlvl3bgcolora]',
                'label' => __('Background Color')
                
            ]
        );
        
        $id = $this->getRequest()->getParam('id');

        if ((empty($data)) && ($id == '')) {
            /* MegaMenu Panel  Options*/
            $data['mmpnl-padding']= '15px 10px 15px 10px';
            $data['mmpnl-bgcolor']= '#FFFFFF';
            $data['mmpnl-bdwidth']= '5px 0px 0px 0px';
            $data['mmpnl-bdcolor']= '#FE5656';
            $data['mmpnl-corner']= '10px 10px 10px 10px';
            $data['mmpnl-clm-padding']= '0px 10px 10px 10px';
             /* First Level Items Options */
            $data['mmlvl1color']='#FE5656';
            $data['mmlvl1size']='15px';
            $data['mmlvl1weight']='700';
            $data['mmlvl1case']='uppercase';
            $data['mmlvl1bgcolor']='#F2F2F2';
            $data['mmlvl1padding']='10px 10px 10px 10px';
            $data['mmlvl1showdivider']= '1';
            $data['mmlvl1dvcolor']='#DDDDDD';
            /* First Level Items on Hover Options */
            $data['mmlvl1colorh']= '#FE5656';
            $data['mmlvl1bgcolorh']='#E3E3E3';
            /* First Level Items on Active Options */
            $data['mmlvl1colora']= '#FE5656';
            $data['mmlvl1bgcolora']='#E3E3E3';
             /* Second Level Items Options */
            $data['mmlvl2color']='#333333';
            $data['mmlvl2size']='14px';
            $data['mmlvl2weight']='600';
            $data['mmlvl2case']='inherit';
            $data['mmlvl2bgcolor']='#FFFFFF';
            $data['mmlvl2padding']='8px 8px 8px 10px';
            $data['mmlvl2showdivider']= '1';
            $data['mmlvl2dvcolor']='#EEEEEE';
            /* Second Level Items on Hover Options */
            $data['mmlvl2colorh']= '#000000';
            $data['mmlvl2bgcolorh']='#F3F3F3';
            /* Second Level Items on Active Options */
            $data['mmlvl2colora']= '#FE5656';
            $data['mmlvl2bgcolora']='#FFFFFF';
             /* Third Level Items Options */
            $data['mmlvl3color']='#333333';
            $data['mmlvl3size']='13px';
            $data['mmlvl3weight']='400';
            $data['mmlvl3case']='inherit';
            $data['mmlvl3bgcolor']='#FFFFFF';
            $data['mmlvl3padding']='8px 8px 8px 20px';
            $data['mmlvl3showdivider']= '1';
            $data['mmlvl3dvcolor']='#EEEEEE';
            /* Third Level Items on Hover Options */
            $data['mmlvl3colorh']= '#000000';
            $data['mmlvl3bgcolorh']='#F3F3F3';
            /* Third Level Items on Active Options */
            $data['mmlvl3colora']= '#FE5656';
            $data['mmlvl3bgcolora']='#FFFFFF';
        }
        
        if ($id) {
        //$form->setValues($model->getData());
        //$informations = json_decode($model->getData('megaoptions'),true);
            $informations = $model->getData();
            if (!empty($informations)) {
                foreach ($informations as $key => $value) :
                    if ($this->isJSON($value)) {
                        $sub_information = json_decode($value, true);
                        foreach ($sub_information as $subkey => $subvalue) :
                            $informations[$subkey] = $subvalue;
                        endforeach;
                    } else {
                        $informations[$key] = $value;
                    }
                endforeach;
                $form->setValues($informations);
            }
            
            /*echo '<pre>';
            print_R($informations);
			die;*/
        } else {
            $form->setValues($data);
        }
        
        $mmpnlbgcolor = $form->getElement('mmpnl-bgcolor');
        $mmpnlbgcolorvalue = $mmpnlbgcolor->getData('value');
        $mmpnlbgcolor->setAfterElementHtml($this->getColorPicker($mmpnlbgcolor, $mmpnlbgcolorvalue));
        
        $mmpnlbdcolor = $form->getElement('mmpnl-bdcolor');
        $mmpnlbdcolorvalue = $mmpnlbdcolor->getData('value');
        $mmpnlbdcolor->setAfterElementHtml($this->getColorPicker($mmpnlbdcolor, $mmpnlbdcolorvalue));
        
        $mmlvl1color = $form->getElement('mmlvl1color');
        $mmlvl1colorvalue = $mmlvl1color->getData('value');
        $mmlvl1color->setAfterElementHtml($this->getColorPicker($mmlvl1color, $mmlvl1colorvalue));
        
        $mmlvl1bgcolor = $form->getElement('mmlvl1bgcolor');
        $mmlvl1bgcolorvalue = $mmlvl1bgcolor->getData('value');
        $mmlvl1bgcolor->setAfterElementHtml($this->getColorPicker($mmlvl1bgcolor, $mmlvl1bgcolorvalue));
        
        $mmlvl1dvcolor = $form->getElement('mmlvl1dvcolor');
        $mmlvl1dvcolorvalue = $mmlvl1dvcolor->getData('value');
        $mmlvl1dvcolor->setAfterElementHtml($this->getColorPicker($mmlvl1dvcolor, $mmlvl1dvcolorvalue));
        
        $mmlvl1colorh = $form->getElement('mmlvl1colorh');
        $mmlvl1colorhvalue = $mmlvl1colorh->getData('value');
        $mmlvl1colorh->setAfterElementHtml($this->getColorPicker($mmlvl1colorh, $mmlvl1colorhvalue));
        
        $mmlvl1bgcolorh = $form->getElement('mmlvl1bgcolorh');
        $mmlvl1bgcolorhvalue = $mmlvl1bgcolorh->getData('value');
        $mmlvl1bgcolorh->setAfterElementHtml($this->getColorPicker($mmlvl1bgcolorh, $mmlvl1bgcolorhvalue));
        
        $mmlvl1colora = $form->getElement('mmlvl1colora');
        $mmlvl1coloravalue = $mmlvl1colora->getData('value');
        $mmlvl1colora->setAfterElementHtml($this->getColorPicker($mmlvl1colora, $mmlvl1coloravalue));
    
        $mmlvl1bgcolora = $form->getElement('mmlvl1bgcolora');
        $mmlvl1bgcoloravalue = $mmlvl1bgcolora->getData('value');
        $mmlvl1bgcolora->setAfterElementHtml($this->getColorPicker($mmlvl1bgcolora, $mmlvl1bgcoloravalue));
    
    
        $mmlvl2color = $form->getElement('mmlvl2color');
        $mmlvl2colorvalue = $mmlvl2color->getData('value');
        $mmlvl2color->setAfterElementHtml($this->getColorPicker($mmlvl2color, $mmlvl2colorvalue));
        
        $mmlvl2bgcolor = $form->getElement('mmlvl2bgcolor');
        $mmlvl2bgcolorvalue = $mmlvl2bgcolor->getData('value');
        $mmlvl2bgcolor->setAfterElementHtml($this->getColorPicker($mmlvl2bgcolor, $mmlvl2bgcolorvalue));
        
        $mmlvl2dvcolor = $form->getElement('mmlvl2dvcolor');
        $mmlvl2dvcolorvalue = $mmlvl2dvcolor->getData('value');
        $mmlvl2dvcolor->setAfterElementHtml($this->getColorPicker($mmlvl2dvcolor, $mmlvl2dvcolorvalue));

        $mmlvl2colorh = $form->getElement('mmlvl2colorh');
        $mmlvl2colorhvalue = $mmlvl2colorh->getData('value');
        $mmlvl2colorh->setAfterElementHtml($this->getColorPicker($mmlvl2colorh, $mmlvl2colorhvalue));
        
        $mmlvl2colora = $form->getElement('mmlvl2colora');
        $mmlvl2coloravalue = $mmlvl2colora->getData('value');
        $mmlvl2colora->setAfterElementHtml($this->getColorPicker($mmlvl2colora, $mmlvl2coloravalue));
        
        $mmlvl2bgcolorh = $form->getElement('mmlvl2bgcolorh');
        $mmlvl2bgcolorhvalue = $mmlvl2bgcolorh->getData('value');
        $mmlvl2bgcolorh->setAfterElementHtml($this->getColorPicker($mmlvl2bgcolorh, $mmlvl2bgcolorhvalue));
        
        $mmlvl2bgcolora = $form->getElement('mmlvl2bgcolora');
        $mmlvl2bgcoloravalue = $mmlvl2bgcolora->getData('value');
        $mmlvl2bgcolora->setAfterElementHtml($this->getColorPicker($mmlvl2bgcolora, $mmlvl2bgcoloravalue));
        
        
        $mmlvl3color = $form->getElement('mmlvl3color');
        $mmlvl3colorvalue = $mmlvl3color->getData('value');
        $mmlvl3color->setAfterElementHtml($this->getColorPicker($mmlvl3color, $mmlvl3colorvalue));
        
        $mmlvl3bgcolor = $form->getElement('mmlvl3bgcolor');
        $mmlvl3bgcolorvalue = $mmlvl3bgcolor->getData('value');
        $mmlvl3bgcolor->setAfterElementHtml($this->getColorPicker($mmlvl3bgcolor, $mmlvl3bgcolorvalue));
        
        $mmlvl3dvcolor = $form->getElement('mmlvl3dvcolor');
        $mmlvl3dvcolorvalue = $mmlvl3dvcolor->getData('value');
        $mmlvl3dvcolor->setAfterElementHtml($this->getColorPicker($mmlvl3dvcolor, $mmlvl3dvcolorvalue));
        
        $mmlvl3colorh = $form->getElement('mmlvl3colorh');
        $mmlvl3colorhvalue = $mmlvl3colorh->getData('value');
        $mmlvl3colorh->setAfterElementHtml($this->getColorPicker($mmlvl3colorh, $mmlvl3colorhvalue));
        
        $mmlvl3bgcolorh = $form->getElement('mmlvl3bgcolorh');
        $mmlvl3bgcolorhvalue = $mmlvl3bgcolorh->getData('value');
        $mmlvl3bgcolorh->setAfterElementHtml($this->getColorPicker($mmlvl3bgcolorh, $mmlvl3bgcolorhvalue));
        
        $mmlvl3colora = $form->getElement('mmlvl3colora');
        $mmlvl3coloravalue = $mmlvl3colora->getData('value');
        $mmlvl3colora->setAfterElementHtml($this->getColorPicker($mmlvl3colora, $mmlvl3coloravalue));
        
        $mmlvl3bgcolora = $form->getElement('mmlvl3bgcolora');
        $mmlvl3bgcoloravalue = $mmlvl3bgcolora->getData('value');
        $mmlvl3bgcolora->setAfterElementHtml($this->getColorPicker($mmlvl3bgcolora, $mmlvl3bgcoloravalue));
        
        
        $this->setChild('form_after', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap($megalvl1dividershow->getHtmlId(), $megalvl1dividershow->getName())
            ->addFieldMap($megalvl1dividercolor->getHtmlId(), $megalvl1dividercolor->getName())
            ->addFieldMap($megalvl2dividershow->getHtmlId(), $megalvl2dividershow->getName())
            ->addFieldMap($megalvl2dividercolor->getHtmlId(), $megalvl2dividercolor->getName())
            ->addFieldMap($megalvl3dividershow->getHtmlId(), $megalvl3dividershow->getName())
            ->addFieldMap($megalvl3dividercolor->getHtmlId(), $megalvl3dividercolor->getName())
            ->addFieldDependence(
                $megalvl1dividercolor->getName(),
                $megalvl1dividershow->getName(),
                '1'
            )
            ->addFieldDependence(
                $megalvl2dividercolor->getName(),
                $megalvl2dividershow->getName(),
                '1'
            )
            ->addFieldDependence(
                $megalvl3dividercolor->getName(),
                $megalvl3dividershow->getName(),
                '1'
            ));
        
        
        $this->setForm($form);
        return parent::_prepareForm();
    }


    public function getColorPicker($element, $value)
    {
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
    public function isJSON($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }
}
