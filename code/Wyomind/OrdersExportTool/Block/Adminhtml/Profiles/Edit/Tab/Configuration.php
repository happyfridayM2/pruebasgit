<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Edit\Tab;

/**
 * Prepare the configuration tab
 */
class Configuration extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Name of the item
     * @var string
     */
    protected $scope = "order";
    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory|null
     */
    protected $_attributeFactory = null;
    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory|null
     */
    protected $_attributeTypeFactory = null;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|null
     */
    protected $_coreDate = null;
    /**
     * @var \Magento\Sales\Model\Order\Config|null
     */
    protected $_orderConfig = null;
    /**
     * @var null|\Wyomind\OrdersExportTool\Helper\Data
     */
    protected $_helper = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\Entity\TypeFactory $attributeTypeFactory
     * @param \Wyomind\OrdersExportTool\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Entity\TypeFactory $attributeTypeFactory,
        \Wyomind\OrdersExportTool\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->_attributeFactory = $attributeFactory;
        $this->_attributeTypeFactory = $attributeTypeFactory;
        $this->_coreDate = $coreDate;
        $this->_orderConfig = $orderConfig;
        $this->_helper = $helper;
    }

    /**
     * Prepare form
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('profile');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('');
        $fieldset = $form->addFieldset('ordersexporttool_profiles_edit_base', ['legend' => __('Export file(s) configuration')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id', 'label' => 'id', 'class' => 'debug']);
        }

        // ===================== action flags ==================================
        // save and generate flag
        $fieldset->addField('generate_i', 'hidden', ['name' => 'generate_i', 'value' => '', 'label' => 'generate_i', 'class' => 'debug']);
        // save and continue flag
        $fieldset->addField('back_i', 'hidden', ['name' => 'back_i', 'value' => '', "label" => 'back_i', 'class' => 'debug']);

        // ===================== required hidden fields ========================
        $fieldset->addField(
            'attributes',
            'hidden',
            [
                'name' => 'attributes',
                'label' => 'attributes',
                'index' => 'attributes',
                'class' => 'debug'
            ]
        );

        // ===================== required visible fields =======================
        // point d'interrogation sur le champs store view
        // $renderer = $this->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element');
        // $field->setRenderer($renderer);
        $fieldset->addField(
            'name',
            'text',
            [
                'label' => __('File name'),
                'class' => 'required-entry validate-no-html-tags',
                'required' => true,
                'name' => 'name',
                'id' => 'name',
                "note" => "<b>" . __("The base name of the exported file(s).") . "</b>"
            ]
        );

        $fieldset->addField(
            'encoding',
            'select',
            [
                'label' => __('Encoding type'),
                'required' => true,
                'class' => 'required-entry',
                'name' => 'encoding',
                'id' => 'encoding',
                'values' => [
                    [
                        'value' => 'UTF-8-without-bom',
                        'label' => 'UTF-8 (without BOM)'
                    ],
                    [
                        'value' => 'UTF-8',
                        'label' => 'UTF-8'
                    ],
                    [
                        'value' => 'Windows-1252',
                        'label' => 'Windows-1252 (ANSI)'
                    ]
                ],
                "note" => "<b>" . __("ANSI or UTF8 encoding. Utf8 is the most common format.") . "</b>"
            ]
        );


        ($model->getName()) ? $fn = $model->getName() : $fn = 'filename';
        switch ($model->getType()) {
            case 1:
                $ext = '.xml';
                break;
            case 2:
                $ext = '.txt';
                break;
            case 3:
                $ext = '.csv';
                break;
            case 4:
                $ext = '.tsv';
                break;
            case 5:
                $ext = '.din';
                break;
            default:
                $ext = '.ext';
        }

        $fieldset->addField(
            'date_format',
            'select',
            [
                'label' => __('File name format '),
                'name' => 'date_format',
                'values' => [
                    [
                        'value' => '{f}',
                        'label' => __($fn) . $ext
                    ],
                    [
                        'value' => 'Y-m-d-{f}',
                        'label' => __($this->_coreDate->date('Y-m-d') . '-' . $fn . $ext)
                    ],
                    [
                        'value' => 'Y-m-d-H-i-s-{f}',
                        'label' => __($this->_coreDate->date('Y-m-d-H-i-s') . '-' . $fn . $ext)
                    ],
                    [
                        'value' => '{f}-Y-m-d',
                        'label' => __($fn . '-' . $this->_coreDate->date('Y-m-d') . $ext)
                    ],
                    [
                        'value' => '{f}-Y-m-d-H-i-s',
                        'label' => __($fn . '-' . $this->_coreDate->date('Y-m-d-H-i-s') . $ext)
                    ],
                    [
                        'value' => 'Y-m-d H-i-s',
                        'label' => __($this->_coreDate->date('Y-m-d H-i-s') . $ext)
                    ]
                ],
                "note" => "<b>" . __("The final name of the exported file(s) including or not the date and/or time.") . "</b>"
            ]

        );
        $fieldset->addField(
            'repeat_for_each',
            'select',
            [
                'label' => __("Create one file for each " . $this->scope),
                'name' => 'repeat_for_each',
                'values' => [
                    [
                        'value' => 0,
                        'label' => __('no')
                    ],
                    [
                        'value' => 1,
                        'label' => __('yes')
                    ]
                ],
                "note" => "<b>" . __("When enabled, each order is exported as a separated file.") . "</b>"
            ]
        );

        $fieldset->addField(
            'repeat_for_each_increment',
            'select',
            [
                'label' => __('File name suffix '),
                'required' => true,
                'class' => 'required-entry',
                'name' => 'repeat_for_each_increment',
                'values' => [
                    [
                        'value' => 1,
                        'label' => 'Order increment ID'
                    ],
                    [
                        'value' => 2,
                        'label' => 'Magento internal order ID'
                    ],
                    [
                        'value' => 3,
                        'label' => 'Module internal auto-increment'
                    ]
                ],
                "note" => "<b>" . __("Each file name will be prefixed with an unique ID, eg: 1000001-export" . $ext . ".") . "</b>"
            ]

        );

        $fieldset = $form->addFieldset('ordersexporttool_profiles_edit_advanced', ['legend' => __('Export options')]);
//        $instances = $this->_helper->getEntities();
//        $values = array();
//        foreach ($instances as $instance) {
//            if ($instance["scopable"]) {
//                $values[] = [
//                    "value" => $instance["code"],
//                    "label" => __($instance["label"])
//                ];
//            }
//        }
//
//        $fieldset->addField(
//            'scope',
//            'select',
//            [
//                'label' => __('Items to export'),
//                'name' => 'scope',
//                'values' => $values,
//
//            ]
//
//
//        );

//        $fieldset->addField(
//            'incremental_column',
//            'select',
//            [
//                'label' => __('Add a counter as the 1st column'),
//                'name' => 'incremental_column',
//                'values' => [
//                    [
//                        'value' => 0,
//                        'label' => __('no')
//                    ],
//                    [
//                        'value' => 1,
//                        'label' => __('yes')
//                    ]
//                ]
//            ]
//        );
//        $fieldset->addField(
//            'incremental_column_name',
//            'text',
//            [
//                'label' => __('Increment column header'),
//                'name' => 'incremental_column_name',
//                'class' => ''
//            ]
//        );


        $fieldset->addField(
            'last_exported_id',
            'text',
            [
                'label' => __('Start with increment ID'),
                'name' => 'last_exported_id',
                "note" => "<b>" . __("From which ID should the " . $this->scope . "s be exported. Leave empty for all " . $this->scope . ".") . "</b>"
            ]
        );


        $fieldset->addField(
            'automatically_update_last_order_id',
            'select',
            [
                'label' => __('Register the latest exported increment ID'),
                'name' => 'automatically_update_last_order_id',
                'values' => [
                    [
                        'value' => 0,
                        'label' => __('no')
                    ],
                    [
                        'value' => 1,
                        'label' => __('yes')
                    ]
                ],
                "note" => "<b>" . __("Automatically update with the latest ID.") . "</b>"
            ]
        );

        $fieldset->addField(
            'flag',
            'select',
            [
                'label' => __("Mark each " . $this->scope . " as exported"),
                'name' => 'flag',
                'values' => [
                    [
                        'value' => 0,
                        'label' => __('no')
                    ],
                    [
                        'value' => 1,
                        'label' => __('yes')
                    ]
                ],
                "note" => "<b>" . __("Mark each exported " . $this->scope . " with the name of the profile. One " . $this->scope . " can receive several flags.") . "</b>"
            ]
        );

        $fieldset->addField(
            'single_export',
            'select',
            [
                'label' => __("Export only unmarked " . $this->scope . ""),
                'name' => 'single_export',
                'values' => [
                    [
                        'value' => 0,
                        'label' => __('no')
                    ],
                    [
                        'value' => 1,
                        'label' => __('yes')
                    ]
                ],
                "note" => "<b>" . __("When enabled, only the " . $this->scope . " that are not yet marked will be exported. The " . $this->scope . " must also match with the filters (store view, " . $this->scope . " increment ID, etc)") . "</b>"
            ]
        );

        $fieldset->addField(
            'update_status',
            'select',
            [
                'label' => __("Update the " . $this->scope . " status"),
                'name' => 'update_status',
                'values' => [
                    [
                        'value' => 0,
                        'label' => __('no')
                    ],
                    [
                        'value' => 1,
                        'label' => __('yes')
                    ]
                ],
                "note" => "<b>" . __("When enabled, the " . $this->scope . " will be updated with a new status.") . "</b>"

            ]
        );

        foreach ($this->_orderConfig->getStates() as $key => $state) {
            $options = [];
            foreach ($this->_orderConfig->getStateStatuses($key) as $k => $s) {
                $options[] = [
                    'value' => $key . '-' . $k,
                    'label' => $s,
                ];
            }

            $values[] = [
                'value' => $options,
                'label' => $state,

            ];
        }

        $fieldset->addField(
            'update_status_to',
            'select',
            [
                'label' => __('New order status'),
                'name' => 'update_status_to',
                'values' => $values,
                "note" => "<b>" . __("Which status to apply to the exported orders.") . "</b>"
            ]
        );
        $fieldset->addField(
            'update_status_message',
            'text',
            [
                'label' => __("Message in the " . $this->scope . " history"),
                'name' => 'update_status_message',
                'class' => '',
                "note" => "<b>" . __("Which comment must be added to the history. Leave empty for none.") . "</b>"
            ]
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
                ->addFieldMap('flag', 'flag')
                ->addFieldMap('single_export', 'single_export')
                ->addFieldDependence('single_export', 'flag', 1)
                ->addFieldMap('repeat_for_each', 'repeat_for_each')
                ->addFieldMap('repeat_for_each_increment', 'repeat_for_each_increment')
                ->addFieldMap('incremental_column', 'incremental_column')
                ->addFieldMap('incremental_column_name', 'incremental_column_name')
                ->addFieldDependence('repeat_for_each_increment', 'repeat_for_each', 1)
                ->addFieldDependence('incremental_column_name', 'incremental_column', 1)
                ->addFieldMap('update_status', 'update_status')
                ->addFieldMap('update_status_to', 'update_status_to')
                ->addFieldMap('scope', 'scope')
                ->addFieldMap('update_status_message', 'update_status_message')
                ->addFieldDependence("update_status_to", "scope", \Wyomind\OrdersExportTool\Helper\Data::ORDER)
                ->addFieldDependence('update_status_to', 'update_status', 1)
                ->addFieldDependence('update_status_message', 'update_status', 1)

        );


        $model->setLibraryUrl($this->getUrl('*/*/library'));
        $model->setLibrarySampleUrl($this->getUrl('*/*/librarysample'));
        $model->setSampleUrl($this->getUrl('*/*/sample'));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Configuration');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Configuration');
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
}