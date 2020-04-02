<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_Builder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Builder\Ui\DataProvider\LinkBuilder\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class LinkModal implements ModifierInterface
{
    /**
     * @var UrlInterface
     * @since 101.0.0
     */
    protected $urlBuilder;

    /**
     * @var ArrayManager
     * @since 101.0.0
     */
    protected $arrayManager;

    /**
     * @param UrlInterface $urlBuilder   
     * @param ArrayManager $arrayManager 
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager
    ) {
        $this->urlBuilder   = $urlBuilder;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->createModal($meta);
        return $meta;
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Create slide-out panel for new category creation
     *
     * @param array $meta
     * @return array
     * @since 101.0.0
     */
    protected function createModal(array $meta)
    {
        return $this->arrayManager->set(
            'link_modal',
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'isTemplate' => false,
                            'componentType' => 'modal',
                            'options' => [
                                'title' => __('Insert/edit link'),
                            ]
                        ]
                    ]
                ],
                'children' => [
                    'link' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => '',
                                    'componentType' => 'container',
                                    'component' => 'Magezon_Builder/js/ui/form/components/linkbuilder-insert',
                                    'dataScope' => 'data',
                                    'update_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                    'render_url' => $this->urlBuilder->getUrl(
                                        'mui/index/render_handle',
                                        [
                                            'handle'  => 'mgz_linkbuilder_form',
                                            'buttons' => 1
                                        ]
                                    ),
                                    'autoRender'       => false,
                                    'ns'               => 'mgz_linkbuilder_form',
                                    'externalProvider' => 'mgz_linkbuilder_form.mgz_linkbuilder_form_data_source',
                                    'toolbarContainer' => '${ $.parentName }',
                                    'formSubmitType'   => 'ajax'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );
    }
}
