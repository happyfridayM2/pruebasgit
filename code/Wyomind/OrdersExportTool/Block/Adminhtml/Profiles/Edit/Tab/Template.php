<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Edit\Tab;

/**
 * Template tab
 */
class Template extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
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

        $fieldset = $form->addFieldset('ordersexporttool_form_file_settings', ['legend' => __('Export file settings')]);


        $fieldset->addField(
            'type',
            'select',
            [
                'label' => __('File type'),
                'required' => true,
                'class' => 'required-entry',
                'name' => 'type',
                'id' => 'type',
                'values' => [
                    [
                        'value' => 1,
                        'label' => 'xml'
                    ],
                    [
                        'value' => 2,
                        'label' => 'txt'
                    ],
                    [
                        'value' => 3,
                        'label' => 'csv'
                    ],
                    [
                        'value' => 4,
                        'label' => 'tsv'
                    ],
                    [
                        'value' => 5,
                        'label' => 'din'
                    ],

                ],
                "note" => "<b>" . __("The extension type of the exported file(s).") . "</b>"
            ]
        );

        $fieldset->addField(
            'format',
            'select',
            [
                'label' => __('File format'),
                'required' => true,
                'class' => 'txt-type required-entry',
                'name' => 'format',
                'id' => 'format',
                'values' => [
                    [
                        'value' => 1,
                        'label' => 'Basic'
                    ],
                    [
                        'value' => 2,
                        'label' => 'Advanced'
                    ],


                ],
                "note" => "<b>" . __("Basic format for txt-like files consists in one optional header and several rows applying the same pattern.") . "<br/>" .
                    __("Advanced format for txt-like files consists in a complex file structure divided into several blocks.") . "</b>"
            ]
        );


        $fieldset->addField(
            'include_header',
            'select',
            [
                'label' => __('Include header'),
                'required' => true,
                'class' => 'required-entry txt-type',
                'name' => 'include_header',
                'id' => 'include_header',
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
                "note" => "<b>" . __("The first line of the file will be the header row") . "</b>"
            ]
        );

        $fieldset->addField(
            'separator',
            'select',
            [
                'label' => __('Delimiter character'),
                'class' => 'txt-type required-entry',
                'id' => 'separator',
                'required' => true,
                'name' => 'separator',
                'style' => '',
                'values' => [
                    [
                        'value' => ';',
                        'label' => ';'
                    ],
                    [
                        'value' => ',',
                        'label' => ','
                    ],
                    [
                        'value' => '|',
                        'label' => '|'
                    ],
                    [
                        'value' => '\t',
                        'label' => 'tab'
                    ],
                    [
                        'value' => '[|]',
                        'label' => '[|]'
                    ]
                ],
                "note" => "<b>" . __("Separator character  for each field of the rows.") . "</b>"
            ]
        );
        $fieldset->addField(
            'protector',
            'select',
            [
                'label' => __('Enclosure character'),
                'class' => 'txt-type not-required',
                'maxlength' => 1,
                'name' => 'protector',
                'values' => [
                    [
                        'value' => '"',
                        'label' => '"'
                    ],
                    [
                        'value' => "'",
                        'label' => "'"
                    ],
                    [
                        'value' => "",
                        'label' => __('none')
                    ]
                ],
                "note" => "<b>" . __("Character that surround each field of the rows.") . "</b>"
            ]
        );

        $fieldset->addField(
            'escaper',
            'select',
            [
                'label' => __('Escape character'),
                'class' => 'txt-type not-required',
                'maxlength' => 1,
                'name' => 'escaper',
                'values' => [
                    [
                        'value' => '\\',
                        'label' => '\\ (backslash)'
                    ],
                    [
                        'value' => "\"",
                        'label' => "\" (quotation mark)"
                    ],
                    [
                        'value' => "",
                        'label' => __('none')
                    ],

                ],
                "note" => "<b>" . __("Character that indicates that next character should not be considered as enclosure.") . "</b>"
            ]
        );

        $fieldset->addField(
            'enclose_data',
            'select',
            [
                'label' => __('Enclose xml tag content inside CDATA (recommended)'),
                'required' => true,
                'class' => 'required-entry xml-type',
                'name' => 'enclose_data',
                'id' => 'enclose_data',
                'values' => [
                    [
                        'value' => 1,
                        'label' => __('yes')
                    ],
                    [
                        'value' => 0,
                        'label' => __('no')
                    ]
                ],
                "note" => "<b>" . __("When enabled all node values are enclose with CDATA which avoid issues with special characters such as < (lower than), > (higher than) or & (ampersand).") . "</b>"
            ]
        );

        $fieldset = $form->addFieldset('ordersexporttool_form_template_settings', ['legend' => __('Template settings')]);
        $fieldset->addField(
            'extra_header',
            'textarea',
            [
                'label' => __('Extra header'),
                'class' => 'txt-type not-required',
                'name' => 'extra_header',
                'style' => 'height:60px;width:500px',
                "note" => "<b>" . __("Additional row(s) of headers that will be added at the very top of the file") . "</b>"

            ]

        );

        $fieldset->addField(
            'header',
            'textarea',
            [
                'label' => __('Header'),
                'class' => '',
                'name' => 'header',
                'required' => true,

                "note" => "<b>" . __("Top of the XML file") . "</b>"
            ]
        );

        $fieldset->addField(
            'body',
            'textarea',
            [
                'label' => __('Body'),
                'class' => '',
                'required' => true,
                'name' => 'body',

                "note" => "<b>" . __("Pattern for the body that will be replicated for each order") . "</b>"
            ]
        );

        $fieldset->addField(
            'footer',
            'textarea',
            [
                'label' => __('Footer'),
                'class' => 'xml-type',
                'required' => true,
                'id' => 'footer',
                'name' => 'footer',

                "note" => "<b>" . __("Bottom of the XML file") . "</b>"
            ]
        );
        $fieldset->addField(
            'extra_footer',
            'textarea',
            [
                'label' => __('Extra footer'),
                'class' => 'txt-type not-required',
                'name' => 'extra_footer',
                'style' => 'height:60px;width:500px',
                "note" => "<b>" . __("Additional row(s) that will be added at the very bottom of the file") . "</b>"

            ]

        );

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
        return __('Template');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Template');
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