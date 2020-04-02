<?php
namespace  Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class Form extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	 public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
       	\Magebees\Navigationmenu\Helper\Data $helper,
        array $data = []
    ) {
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

        if ($model->getId()) {
            $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Edit Group "'.$title.'" Information')]);
        } else {
            $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Add Group')]);
        }
            

        if ($model->getId()) {
            $fieldset->addField('group_id', 'hidden', ['name' => 'group_id']);
        }
        
        /* Get object of helper using object manager*/
        
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
                'disabled' => $isElementDisabled,
            ]
        );
        $fieldset->addField(
            'showhidetitle',
            'select',
            [
                'name' => 'showhidetitle',
                'label' => __('Show Hide Group Title'),
                'title' => __('Show Hide Group Title'),
                'required' => true,
                'values' =>$this->_helper->getShowHideTitle()
            ]
        );
        $fieldset->addField(
            'titlecolor',
            'text',
            [
                'name' => 'root[titlecolor]',
                'label' => __('Title Color'),
            ]
        );
        
        $fieldset->addField(
            'titlebgcolor',
            'text',
            [
                'name' => 'root[titlebgcolor]',
                'label' => __('Title Background Color')
                
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'values' =>$this->_helper->getOptionArray()
            ]
        );

        
        
        $menu_type=$fieldset->addField(
            'menutype',
            'select',
            [
                'name' => 'menutype',
                'label' => __('Menu Type'),
                'title' => __('Menu Type'),
                'onchange' => 'menutaboptions(this.value);',
                'onload' => 'menutaboptions(this.value);',
                'required' => true,
                'values' =>$this->_helper->getGroupMenuType()
            ]
        );
         $position=$fieldset->addField(
             'position',
             'select',
             [
                'name' => 'position',
                'label' => __('Alignment'),
                'title' => __('Alignment'),
                'required' => true,
                'values' =>$this->_helper->getAlignmentType()
             ]
         );
          $fieldset->addField(
              'level',
              'select',
              [
                'name' => 'level',
                'label' => __('Item Level'),
                'title' => __('Item Level'),
                'required' => true,
                'values' =>$this->_helper->getMenuLevel()
              ]
          );
          $fieldset->addField(
              'direction',
              'select',
              [
                'name' => 'direction',
                'label' => __('Direction'),
                'title' => __('Direction'),
                'required' => true,
                'values' =>$this->_helper->getDirection()
              ]
          );
          $fieldset->addField(
              'image_height',
              'text',
              [
                'name' => 'image_height',
                'label' => __('Image height'),
                'title' => __('Image height'),
                'required' => true,
                'class' => 'required-entry validate-number',
                'after_element_html' => '<small>Image height set in px</small>'
              ]
          );
          $fieldset->addField(
              'image_width',
              'text',
              [
                'name' => 'image_width',
                'label' => __('Image width'),
                'title' => __('Image width'),
                'required' => true,
                'class' => 'required-entry validate-number',
                'after_element_html' => '<small>Image width set in px</small>'
              ]
          );
        $id = $this->getRequest()->getParam('id');
        if ((empty($data)) && ($id == '')) {
            $data['titlecolor']='#FFFFFF';
            $data['titlebgcolor']='#122736';
            $data['image_height'] = '18';
            $data['image_width'] = '18';
            $data['level'] = '5';
        }
        if ($id) {
        /*$form->setValues($model->getData());*/
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
        
        $titlecolor = $form->getElement('titlecolor');
        $titlecolorvalue = $titlecolor->getData('value');
        $titlecolor->setAfterElementHtml($this->getColorPicker($titlecolor, $titlecolorvalue));
        $titlebgcolor = $form->getElement('titlebgcolor');
        $titlebgcolorvalue = $titlebgcolor->getData('value');
        $titlebgcolor->setAfterElementHtml($this->getColorPicker($titlebgcolor, $titlebgcolorvalue));
        $this->setForm($form);
        $this->setChild('form_after', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap($menu_type->getHtmlId(), $menu_type->getName())
            ->addFieldMap($position->getHtmlId(), $position->getName())
            ->addFieldDependence(
                $position->getName(),
                $menu_type->getName(),
                'mega-menu'
            ));

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
