<?php
namespace  Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class Submenuitem extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        
        $fieldset = $form->addFieldset('subitems_form_color', ['legend' =>__('Flyout Panel')]);
        if ($model->getId()) {
            $fieldset->addField('group_id', 'hidden', ['name' => 'group_id']);
        }
        
        $fieldset->addField('ddpnl-width', 'text', [
            'label' => __('Width'),
            'name' => 'fly[ddpnl-width]'
        ]);
        $fieldset->addField('ddpnl-padding', 'text', [
            'label' => __('Padding'),
            'name' => 'fly[ddpnl-padding]',
            'note' => 'Ex: 5px 10px 5px 10px (Top Right Bottom Left)'
        ]);
        $fieldset->addField('ddpnl-bgcolor', 'text', [
            'label' => __('Background Color'),
            'name' => 'fly[ddpnl-bgcolor]'
            
        ]);
        $fieldset->addField('ddpnl-bdwidth', 'text', [
            'label' => __('Border Width'),
            'name' => 'fly[ddpnl-bdwidth]',
            'note' => 'Ex: 2px 4px 4px 2px (Top Right Bottom Left)'
            
        ]);
        
        $fieldset->addField('ddpnl-bdcolor', 'text', [
            'label' => __('Border Color'),
            'name' => 'fly[ddpnl-bdcolor]'
            
        ]);
        $fieldset->addField('ddpnl-corner', 'text', [
            'label' => __('Round Corners'),
            'name' => 'fly[ddpnl-corner]',
            'note' => 'Ex: 5px 10px 5px 10px (Top-Left Top-Right Bottom-Right Bottom-Left)'
        ]);
        
        $fieldset = $form->addFieldset('flysubitem_form_color', ['legend' =>__('Flyout Menu Items')]);
        
        $fieldset->addField(
            'ddlinkcolor',
            'text',
            [
                'name' => 'fly[ddlinkcolor]',
                'label' => __('Font Color')
                
            ]
        );
        $fieldset->addField(
            'ddlinksize',
            'text',
            [
                'name' => 'fly[ddlinksize]',
                'label' => __('Font Size'),
            ]
        );
        $fieldset->addField(
            'ddlinkweight',
            'text',
            [
                'name' => 'fly[ddlinkweight]',
                'label' => __('Font Weight'),
                
            ]
        );
        $fieldset->addField(
            'ddlinkcase',
            'select',
            [
                'name' => 'fly[ddlinkcase]',
                'label' => __('Font Transform'),
                'values' =>$this->_helper->getFontTransform()
            ]
        );
        
        $fieldset->addField(
            'ddlinkbgcolor',
            'text',
            [
                'name' => 'fly[ddlinkbgcolor]',
                'label' => __('Background Color')
                
            ]
        );
        $fieldset->addField(
            'ddlinkpadding',
            'text',
            [
                'name' => 'fly[ddlinkpadding]',
                'label' => __('Padding'),
                'note' => 'Ex : Top Right Bottom Left',
            ]
        );
        
        $dddividershow = $fieldset->addField(
            'ddshowdivider',
            'select',
            [
                'name' => 'fly[ddshowdivider]',
                'label' => __('Show Divider'),
                'title' => __('Show Divider'),
                'values' =>$this->_yesno->toOptionArray(),
            ]
        );
        
        $dddividercolor = $fieldset->addField(
            'ddlinkdvcolor',
            'text',
            [
                'name' => 'fly[ddlinkdvcolor]',
                'label' => __('Divider Color')
                
            ]
        );
        
        $fieldset = $form->addFieldset('flysubitemitem_hover', ['legend' =>__('Flyout Menu Items on Hover')]);
        $fieldset->addField(
            'ddlinkcolorh',
            'text',
            [
                'name' => 'fly[ddlinkcolorh]',
                'label' => __('Font Color')
                
            ]
        );
        $fieldset->addField(
            'ddlinkbgcolorh',
            'text',
            [
                'name' => 'fly[ddlinkbgcolorh]',
                'label' => __('Background Color')
                
            ]
        );
        $fieldset = $form->addFieldset('flysubitemitem_active', ['legend' =>__('Flyout Menu Items on Active')]);
        $fieldset->addField(
            'ddlinkcolora',
            'text',
            [
                'name' => 'fly[ddlinkcolora]',
                'label' => __('Font Color')
                
            ]
        );
        $fieldset->addField(
            'ddlinkbgcolora',
            'text',
            [
                'name' => 'fly[ddlinkbgcolora]',
                'label' => __('Background Color')
                
            ]
        );
        $this->setChild('form_after', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap($dddividershow->getHtmlId(), $dddividershow->getName())
            ->addFieldMap($dddividercolor->getHtmlId(), $dddividercolor->getName())
            ->addFieldDependence(
                $dddividercolor->getName(),
                $dddividershow->getName(),
                '1'
            ));
        $id = $this->getRequest()->getParam('id');

        if ((empty($data)) && ($id == '')) {
            /* Flyout Panel  Options*/
            $data['ddpnl-width']='200px';
            $data['ddpnl-padding']='0px 0px 0px 0px';
            $data['ddpnl-bgcolor']='#FFFFFF';
            $data['ddpnl-bdwidth']='5px 0px 0px 0px';
            $data['ddpnl-bdcolor']='#FE5656';
            $data['ddpnl-corner']='0px 5px 5px 5px';
            /*  Flyout Menu Items Options*/
            $data['ddlinkcolor']='#333333';
            $data['ddlinksize']='14px';
            $data['ddlinkweight']='700';
            $data['ddlinkcase']='inherit';
            $data['ddlinkbgcolor']='#F3F3F3';
            $data['ddlinkpadding']='8px 10px 8px 10px';
            $data['ddshowdivider']='1';
            $data['ddlinkdvcolor']='#DDDDDD';
            /*  Flyout Menu Items on Hover Options*/
            $data['ddlinkcolorh']='#FE5656';
            $data['ddlinkbgcolorh']='#EEEEEE';
            /*  Flyout Menu Items on Active Options*/
            $data['ddlinkcolora']='#FE5656';
            $data['ddlinkbgcolora']='#FFFFFF';
        }
        
       
        
            
        if ($id) {
        //$form->setValues($model->getData());
            $informations = $model->getData();
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
        } else {
            $form->setValues($data);
        }
          
         $ddpnlbgcolor = $form->getElement('ddpnl-bgcolor');
        $ddpnlbgcolorvalue = $ddpnlbgcolor->getData('value');
        $ddpnlbgcolor->setAfterElementHtml($this->getColorPicker($ddpnlbgcolor, $ddpnlbgcolorvalue));
        
        $ddpnlbdcolor = $form->getElement('ddpnl-bdcolor');
        $ddpnlbdcolorvalue = $ddpnlbdcolor->getData('value');
        $ddpnlbdcolor->setAfterElementHtml($this->getColorPicker($ddpnlbdcolor, $ddpnlbdcolorvalue));
        
        $ddlinkcolor = $form->getElement('ddlinkcolor');
        $ddlinkcolorvalue = $ddlinkcolor->getData('value');
        $ddlinkcolor->setAfterElementHtml($this->getColorPicker($ddlinkcolor, $ddlinkcolorvalue));
        
        $ddlinkbgcolor = $form->getElement('ddlinkbgcolor');
        $ddlinkbgcolorvalue = $ddlinkbgcolor->getData('value');
        $ddlinkbgcolor->setAfterElementHtml($this->getColorPicker($ddlinkbgcolor, $ddlinkbgcolorvalue));
        
        $ddlinkdvcolor = $form->getElement('ddlinkdvcolor');
        $ddlinkdvcolorvalue = $ddlinkdvcolor->getData('value');
        $ddlinkdvcolor->setAfterElementHtml($this->getColorPicker($ddlinkdvcolor, $ddlinkdvcolorvalue));
        
        $ddlinkcolorh = $form->getElement('ddlinkcolorh');
        $ddlinkcolorhvalue = $ddlinkcolorh->getData('value');
        $ddlinkcolorh->setAfterElementHtml($this->getColorPicker($ddlinkcolorh, $ddlinkcolorhvalue));
        
        $ddlinkbgcolorh = $form->getElement('ddlinkbgcolorh');
        $ddlinkbgcolorhvalue = $ddlinkbgcolorh->getData('value');
        $ddlinkbgcolorh->setAfterElementHtml($this->getColorPicker($ddlinkbgcolorh, $ddlinkbgcolorhvalue));
        
        $ddlinkcolora = $form->getElement('ddlinkcolora');
        $ddlinkcoloravalue = $ddlinkcolora->getData('value');
        $ddlinkcolora->setAfterElementHtml($this->getColorPicker($ddlinkcolora, $ddlinkcoloravalue));
        
        $ddlinkbgcolora = $form->getElement('ddlinkbgcolora');
        $ddlinkbgcoloravalue = $ddlinkbgcolora->getData('value');
        $ddlinkbgcolora->setAfterElementHtml($this->getColorPicker($ddlinkbgcolora, $ddlinkbgcoloravalue));
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
