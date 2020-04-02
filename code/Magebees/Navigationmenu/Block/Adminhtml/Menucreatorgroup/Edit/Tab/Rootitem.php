<?php
namespace  Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class Rootitem extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        $fieldset = $form->addFieldset('groupmenu_form_color', ['legend' =>__('Menu Bar')]);
        
        if ($model->getId()) {
            $fieldset->addField('group_id', 'hidden', ['name' => 'group_id']);
        }
        
        /* Get object of helper using object manager*/
         $fieldset->addField(
             'menubgcolor',
             'text',
             [
                'name' => 'root[menubgcolor]',
                'label' => __('Background Color')
               
                
             ]
         );
        
        $fieldset->addField(
            'menupadding',
            'text',
            [
                'name' => 'root[menupadding]',
                'label' => __('Padding'),
                'note' => 'Ex: 5px 10px 5px 10px (Top Right Bottom Left)',
            ]
        );
        $fieldset->addField(
            'menu_width',
            'text',
            [
                'name' => 'root[menu_width]',
                'label' => __('Menu Width'),
                'title' => __('Menu Width'),
                'required' => false,
                'after_element_html' => '<small>Set in px</small>'
            ]
        );
        

        $fieldset = $form->addFieldset('dropdown_form_color_items', ['legend' =>__('Root Level Items')]);
        $fieldset->addField(
            'lvl0color',
            'text',
            [
                'name' => 'root[lvl0color]',
                'label' => __('Font Color')
                 
            ]
        );
        $fieldset->addField(
            'lvl0size',
            'text',
            [
                'name' => 'root[lvl0size]',
                'label' => __('Font Size'),
            ]
        );
        $fieldset->addField(
            'lvl0weight',
            'text',
            [
                'name' => 'root[lvl0weight]',
                'label' => __('Font Weight'),
            ]
        );
        $fieldset->addField(
            'lvl0case',
            'select',
            [
                'name' => 'root[lvl0case]',
                'label' => __('Font Transform'),
                'values' =>$this->_helper->getFontTransform()
            ]
        );
        $fieldset->addField(
            'lvl0bgcolor',
            'text',
            [
                'name' => 'root[lvl0bgcolor]',
                'label' => __('Background Color')
                
            ]
        );
        $fieldset->addField(
            'lvl0padding',
            'text',
            [
                'name' => 'root[lvl0padding]',
                'label' => __('Padding'),
                'note' => 'Ex: 10px 15px 10px 15px (Top Right Bottom Left)',
            ]
        );
        
        $fieldset->addField(
            'lvl0corner',
            'text',
            [
                'name' => 'root[lvl0corner]',
                'label' => __('Rounded Corners'),
                'note' => 'Ex: 5px 10px 5px 10px (Top-left Top-Right Bottom-Right Bottom-Left)',
                
            ]
        );
        $dividershow = $fieldset->addField(
            'lvl0showdivider',
            'select',
            [
                'name' => 'root[lvl0showdivider]',
                'label' => __('Show Divider'),
                'title' => __('Show Divider'),
                'values' =>$this->_yesno->toOptionArray(),
            ]
        );
        
        $dividercolor = $fieldset->addField(
            'lvl0dvcolor',
            'text',
            [
                'name' => 'root[lvl0dvcolor]',
                'label' => __('Divider Color')
                
            ]
        );
        $fieldset->addField(
            'lvl0arw',
            'select',
            [
                'name' => 'root[lvl0arw]',
                'label' => __('Show Arrow'),
                'title' => __('Show Divider'),
                'values' =>$this->_yesno->toOptionArray(),
            ]
        );
            
        $fieldset = $form->addFieldset('top_link_hover', ['legend' =>__('Root Level Items on Hover')]);
        $fieldset->addField(
            'lvl0colorh',
            'text',
            [
                'name' => 'root[lvl0colorh]',
                'label' => __('Font Color'),
                
            ]
        );
        
        $fieldset->addField(
            'lvl0bgcolorh',
            'text',
            [
                'name' => 'root[lvl0bgcolorh]',
                'label' => __('Background Color')
                
            ]
        );
        
        $fieldset = $form->addFieldset('top_link_active', ['legend' =>__('Root Level Items on Active')]);
        $fieldset->addField(
            'lvl0colora',
            'text',
            [
                'name' => 'root[lvl0colora]',
                'label' => __('Font Color')
                
            ]
        );
        $fieldset->addField(
            'lvl0bgcolora',
            'text',
            [
                'name' => 'root[lvl0bgcolora]',
                'label' => __('Background Color')
                
            ]
        );
        
        $id = $this->getRequest()->getParam('id');
        if ((empty($data)) && ($id == '')) {
        /* Menu Bar Options */
            $data['menubgcolor'] = '#F2F2F2';
            $data['menupadding']='0px 0px 0px 0px';
            $data['menu_width']='1200px';
            /*  Root Level Items  Options */
            $data['lvl0color']='#122736';
            $data['lvl0size']='15px';
            $data['lvl0weight']='bold';
            $data['lvl0case']='uppercase';
            $data['lvl0bgcolor']='#F2F2F2';
            $data['lvl0padding']='10px 15px 10px 15px';
            //$data['lvl0spaccing']='0';
            $data['lvl0corner']='0px 0px 0px 0px';
            $data['lvl0showdivider']='1';
            $data['lvl0dvcolor']='#E1E1E1';
            $data['lvl0arw']='1';
            /*Root Level Items on Hover Options */
            $data['lvl0colorh'] = '#FFFFFF';
            $data['lvl0bgcolorh'] = '#FE5656';
            /* Root Level Items on Active Options*/
            $data['lvl0colora'] = '#FE5656';
            $data['lvl0bgcolora'] = '#FFFFFF';
        }
        
        if ($id) {
            $informations = $model->getData();
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
        } else {
            $form->setValues($data);
        }
        
        
        
        
       
        $menubgcolor = $form->getElement('menubgcolor');
        $menubgcolorvalue = $menubgcolor->getData('value');
        $menubgcolor->setAfterElementHtml($this->getColorPicker($menubgcolor, $menubgcolorvalue));
        
        $lvl0color = $form->getElement('lvl0color');
        $lvl0colorvalue = $lvl0color->getData('value');
        $lvl0color->setAfterElementHtml($this->getColorPicker($lvl0color, $lvl0colorvalue));
        
        $lvl0bgcolor = $form->getElement('lvl0bgcolor');
        $lvl0bgcolorvalue = $lvl0bgcolor->getData('value');
        $lvl0bgcolor->setAfterElementHtml($this->getColorPicker($lvl0bgcolor, $lvl0bgcolorvalue));
        
        $dividercolor = $form->getElement('lvl0dvcolor');
        $lvl0dvcolorvalue = $dividercolor->getData('value');
        $dividercolor->setAfterElementHtml($this->getColorPicker($dividercolor, $lvl0dvcolorvalue));
        
        $lvl0colorh = $form->getElement('lvl0colorh');
        $lvl0colorhvalue = $lvl0colorh->getData('value');
        $lvl0colorh->setAfterElementHtml($this->getColorPicker($lvl0colorh, $lvl0colorhvalue));
        
        $lvl0bgcolorh = $form->getElement('lvl0bgcolorh');
        $lvl0bgcolorhvalue = $lvl0bgcolorh->getData('value');
        $lvl0bgcolorh->setAfterElementHtml($this->getColorPicker($lvl0bgcolorh, $lvl0bgcolorhvalue));
        
        $lvl0colora = $form->getElement('lvl0colora');
        $lvl0coloravalue = $lvl0colora->getData('value');
        $lvl0colora->setAfterElementHtml($this->getColorPicker($lvl0colora, $lvl0coloravalue));
        
        $lvl0bgcolora = $form->getElement('lvl0bgcolora');
        $lvl0bgcoloravalue = $lvl0bgcolora->getData('value');
        $lvl0bgcolora->setAfterElementHtml($this->getColorPicker($lvl0bgcolora, $lvl0bgcoloravalue));
        
         $this->setChild('form_after', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap($dividershow->getHtmlId(), $dividershow->getName())
            ->addFieldMap($dividercolor->getHtmlId(), $dividercolor->getName())
            ->addFieldDependence(
                $dividercolor->getName(),
                $dividershow->getName(),
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
