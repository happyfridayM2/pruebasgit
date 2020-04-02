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

namespace Magezon\Builder\Data\Element;

class NewsletterForm extends \Magezon\Builder\Data\Element\AbstractElement
{
    /**
     * @return Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareGeneralTab()
    {
    	$general = parent::prepareGeneralTab();

    		$container1 = $general->addContainerGroup(
	            'container1',
	            [
					'sortOrder' => 10
	            ]
		    );

		    	$container1->addChildren(
		            'title',
		            'text',
		            [
						'sortOrder'       => 10,
						'key'             => 'title',
						'defaultValue'    => 'Subscribe to Our Newsletter',
						'templateOptions' => [
							'label'        => __('Title')
		                ]
		            ]
		        );

		        $container1->addChildren(
		            'title_tag',
		            'select',
		            [
						'sortOrder'       => 20,
						'key'             => 'title_tag',
						'defaultValue'    => 'h3',
						'className'       => 'mgz-width30',
						'templateOptions' => [
							'label'   => __('Title Tag'),
							'options' => $this->getHeadingType()
		                ]
		            ]
		        );

    		$container2 = $general->addContainerGroup(
	            'container2',
	            [
					'sortOrder'      => 20,
					'hideExpression' => '!model.title'
	            ]
		    );

	    		$container2->addChildren(
	    			'title_color',
	    			'color',
	    			[
	    				'sortOrder'       => 10,
	    				'key'             => 'title_color',
	    				'templateOptions' => [
	    					'label' => __('Title Color')
	    				]
	    			]
	    		);

	    		$container2->addChildren(
	    			'title_spacing',
	    			'text',
	    			[
	    				'sortOrder'       => 20,
	    				'key'             => 'title_spacing',
	    				'templateOptions' => [
	    					'label' => __('Title Spacing')
	    				]
	    			]
	    		);

    		$container3 = $general->addContainerGroup(
	            'container3',
	            [
					'sortOrder'      => 30,
					'hideExpression' => '!model.title'
	            ]
		    );

	    		$container3->addChildren(
	    			'title_font_size',
	    			'text',
	    			[
	    				'sortOrder'       => 10,
	    				'key'             => 'title_font_size',
	    				'templateOptions' => [
	    					'label' => __('Title Font Size')
	    				]
	    			]
	    		);

	    		$container3->addChildren(
	    			'title_font_weight',
	    			'text',
	    			[
	    				'sortOrder'       => 20,
	    				'key'             => 'title_font_weight',
	    				'templateOptions' => [
	    					'label' => __('Title Font Weight')
	    				]
	    			]
	    		);

	    	$general->addChildren(
	            'description',
	            'text',
	            [
					'sortOrder'       => 40,
					'key'             => 'description',
					'defaultValue'    => 'Signup for our news, special offers, product updates.',
					'templateOptions' => [
						'label'   => __('Description')
	                ]
	            ]
	        );

	    	$general->addChildren(
	            'form_width',
	            'text',
	            [
					'sortOrder'       => 50,
					'key'             => 'form_width',
					'templateOptions' => [
						'label'   => __('Width')
	                ]
	            ]
	        );

    	return $general;
    }
}