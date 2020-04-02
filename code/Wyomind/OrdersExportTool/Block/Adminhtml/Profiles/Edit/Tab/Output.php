<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Edit\Tab;

/**
 * Output tab
 */
class Output extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Prepare form
     * @return $this
     */
    protected function _prepareForm()
    {
        $model=$this->_coreRegistry->registry('profile');
        $form=$this->_formFactory->create();
        $form->setHtmlIdPrefix('');

        $fieldset=$form->addFieldset('storage', ['legend'=>__('Storage settings')]);
        $fieldset->addField(
            'storage_enabled',
            'select',
            [
                'label'=>__('Store the file on server'),
                'name'=>'storage_enabled',
                'required'=>true,
                'values'=>[
                    [
                        'value'=>0,
                        'label'=>__('no')
                    ],
                    [
                        'value'=>1,
                        'label'=>__('yes')
                    ]
                ]
            ]
        );

        $fieldset->addField(
            'path',
            'text',
            [
                'label'=>__('File directory'),
                'name'=>'path',
                'required'=>true,
                'value'=>$model->getFilePath()
            ]
        );

        $fieldset=$form->addFieldset('ftp', ['legend'=>__('FTP settings')]);

        $fieldset->addField(
            'ftp_enabled',
            'select',
            [
                'label'=>__('Upload by FTP'),
                'name'=>'ftp_enabled',
                'required'=>true,
                'values'=>[
                    [
                        'value'=>0,
                        'label'=>__('no')
                    ],
                    [
                        'value'=>1,
                        'label'=>__('yes')
                    ]
                ]
            ]
        );

        $fieldset->addField(
            'use_sftp',
            'select',
            [
                'label'=>__('Use sftp'),
                'name'=>'use_sftp',
                'required'=>true,
                'values'=>[
                    [
                        'value'=>0,
                        'label'=>__('no')
                    ],
                    [
                        'value'=>1,
                        'label'=>__('yes')
                    ]
                ]
            ]
        );

        $fieldset->addField(
            'ftp_active',
            'select',
            [
                'label'=>__('Use passive mode'),
                'name'=>'ftp_active',
                'required'=>true,
                'values'=>[
                    [
                        'value'=>0,
                        'label'=>__('no')
                    ],
                    [
                        'value'=>1,
                        'label'=>__('yes')
                    ]
                ],

            ]
        );
        $fieldset->addField(
            'ftp_host',
            'text',
            [
                'label'=>__('Host'),
                'name'=>'ftp_host'
            ]
        );
        $fieldset->addField(
            'ftp_port',
            'text',
            [
                'label'=>__('Port'),
                'name'=>'ftp_port'
            ]
        );
        $fieldset->addField(
            'ftp_login',
            'text',
            [
                'label'=>__('Login'),
                'name'=>'ftp_login'
            ]
        );
        $fieldset->addField(
            'ftp_password',
            'password',
            [
                'label'=>__('Password'),
                'name'=>'ftp_password'
            ]
        );
        $fieldset->addField(
            'ftp_dir',
            'text',
            [
                'label'=>__('Destination directory'),
                'name'=>'ftp_dir',
                'note'=>__("<a style='margin:10px; display:block;' href='javascript:void(require([\"oet_ftp\"], function (ftp) { ftp.test(\"%1\")}))'>Test Connection</a>", $this->getUrl('*/*/ftp'))

            ]

        );


        $fieldset=$form->addFieldset('mail', ['legend'=>__('Email settings')]);

        $fieldset->addField(
            'mail_enabled',
            'select',
            [
                'label'=>__('Send by email'),
                'name'=>'mail_enabled',
                'required'=>true,
                'values'=>[
                    [
                        'value'=>0,
                        'label'=>__('no')
                    ],
                    [
                        'value'=>1,
                        'label'=>__('yes')
                    ]
                ]
            ]
        );
        $fieldset->addField(
            'mail_sender',
            'text',
            [
                'label'=>__('Email sender'),
                'name'=>'mail_sender',
                "required"=>true
            ]
        );
        $fieldset->addField(
            'mail_recipients',
            'text',
            [
                'label'=>__('Email recipients'),
                'name'=>'mail_recipients',
                "required"=>true
            ]
        );

        $fieldset->addField(
            'mail_subject',
            'text',
            [
                'label'=>__('Email subject'),
                'name'=>'mail_subject',
                "required"=>true
            ]
        );

        $fieldset->addField(
            'mail_message',
            'textarea',
            [
                'label'=>__('Email body'),
                'name'=>'mail_message',
                'note'=>__("<a style='margin:10px; display:block;' href='javascript:void(require([\"oet_email\"], function (email) { email.test(\"%1\")}))'>Test Email</a>", $this->getUrl('*/*/email'))

            ]
        );

        $fieldset->addField(
            'mail_one_report',
            'select',
            [
                'label'=>__('Send all files in the same email'),
                'name'=>'mail_one_report',
                'values'=>[
                    [
                        'value'=>0,
                        'label'=>__('no')
                    ],
                    [
                        'value'=>1,
                        'label'=>__('yes')
                    ]
                ]
            ]
        );

        $fieldset->addField(
            'mail_zip',
            'select',
            [
                'label'=>__('Send all files in a zipped file'),
                'name'=>'mail_zip',
                'values'=>[
                    [
                        'value'=>0,
                        'label'=>__('no')
                    ],
                    [
                        'value'=>1,
                        'label'=>__('yes')
                    ]
                ],

            ]
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
                ->addFieldMap('ftp_enabled', 'ftp_enabled')
                ->addFieldMap('ftp_host', 'ftp_host')
                ->addFieldMap('ftp_port', 'ftp_port')
                ->addFieldMap('ftp_login', 'ftp_login')
                ->addFieldMap('ftp_password', 'ftp_password')
                ->addFieldMap('ftp_dir', 'ftp_dir')
                ->addFieldMap('ftp_active', 'ftp_active')
                ->addFieldMap('use_sftp', 'use_sftp')
                ->addFieldMap('mail_enabled', 'mail_enabled')
                ->addFieldMap('mail_subject', 'mail_subject')
                ->addFieldMap('mail_recipients', 'mail_recipients')
                ->addFieldMap('mail_sender', 'mail_sender')
                ->addFieldMap('repeat_for_each', 'repeat_for_each')
                ->addFieldMap('mail_zip', 'mail_zip')
                ->addFieldMap('mail_message', 'mail_message')
                ->addFieldMap('mail_one_report', 'mail_one_report')
                ->addFieldMap('storage_enabled', 'storage_enabled')
                ->addFieldMap('path', 'path')
                ->addFieldDependence('ftp_host', 'ftp_enabled', 1)
                ->addFieldDependence('ftp_port', 'ftp_enabled', 1)
                ->addFieldDependence('ftp_login', 'ftp_enabled', 1)
                ->addFieldDependence('ftp_password', 'ftp_enabled', 1)
                ->addFieldDependence('ftp_active', 'ftp_enabled', 1)
                ->addFieldDependence('use_sftp', 'ftp_enabled', 1)
                ->addFieldDependence('ftp_active', 'use_sftp', 0)
                ->addFieldDependence('ftp_dir', 'ftp_enabled', 1)
                ->addFieldDependence('ftp_active', 'use_sftp', 0)
                ->addFieldDependence('mail_subject', 'mail_enabled', 1)
                ->addFieldDependence('mail_message', 'mail_enabled', 1)
                ->addFieldDependence('mail_one_report', 'mail_enabled', 1)
                ->addFieldDependence('mail_recipients', 'mail_enabled', 1)
                ->addFieldDependence('mail_sender', 'mail_enabled', 1)
                ->addFieldDependence('mail_zip', 'repeat_for_each', 1)
                ->addFieldDependence('mail_one_report', 'repeat_for_each', 1)
                ->addFieldDependence('mail_zip', 'mail_one_report', 1)
                ->addFieldDependence('path', 'storage_enabled', 1)
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
        return __('Output');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Output');
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