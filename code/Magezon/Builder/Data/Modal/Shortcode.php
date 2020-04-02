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

namespace Magezon\Builder\Data\Modal;

class Shortcode extends \Magezon\Builder\Data\Element\AbstractElement
{
	public function prepareForm()
    {
    	$general = $this->addTab(
            self::TAB_GENERAL,
            [
                'sortOrder'       => 0,
                'templateOptions' => [
                    'label' => __('General')
                ]
            ]
        );

            $general->addChildren(
                'shortcode',
                'textarea',
                [
                    'sortOrder'       => 10,
                    'key'             => 'shortcode',
                    'templateOptions' => [
                        'rows' => 20
                    ]
                ]
            );

    	return $this;
    }
}