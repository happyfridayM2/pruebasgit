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

namespace Magezon\Builder\Block;

abstract class Style extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var string
	 */
	protected $_template = 'Magezon_Builder::style/default.phtml';

	/**
	 * @var \Magezon\Builder\Helper\Data
	 */
	protected $dataHelper;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context    
	 * @param \Magezon\Builder\Helper\Data                     $dataHelper 
	 * @param array                                            $data       
	 */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magezon\Builder\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param  string  $value       
     * @param  boolean $isImportant 
     * @return string               
     */
	public function getStyleProperty($value, $isImportant = false)
	{
		return $this->dataHelper->getStyleProperty($value, $isImportant);
	}

	/**
	 * @param  string  $value       
	 * @param  boolean $isImportant 
	 * @return string               
	 */
	public function getStyleColor($value, $isImportant = false)
	{
		return $this->dataHelper->getStyleColor($value, $isImportant);
	}

	/**
	 * @param  array $styles 
	 * @return string       
	 */
	public function parseStyles($styles)
	{
		return $this->dataHelper->parseStyles($styles);
	}

	/**
	 * @param  string|array $target 
	 * @param  array $styles 
	 * @param  string $suffix 
	 * @return string         
	 */
	public function getStyles($target, $styles, $suffix = '')
	{
		$htmlId = $this->getHtmlId();
		$html   = '';
		if (is_array($target)) {
			foreach ($target as $k => $_selector) {
				if (!$_selector) {
					unset($target[$k]);
				}
			}
			$i = 0;
			$count = count($target);
			foreach ($target as $_selector) {
				$html .= $htmlId . ' ' . $_selector . $suffix;
				if ($i!=$count-1)  {
					$html .= ',';
				}
				$i++;
			}
		} else {
			$html = $htmlId . ' ' . $target . $suffix;
		}
		$stylesHtml = $this->parseStyles($styles);
		if (!$stylesHtml) return;
		if ($styles) {
			$html .= '{';
			$html .= $stylesHtml;
			$html .= '}';
		}
		return $html;
	}

	/**
	 * @return string
	 */
	public function getHtmlId()
	{
		return '.mgz-element.' . $this->getElement()->getHtmlId();
	}

	/**
	 * @return string
	 */
	public function getOwlCarouselStyles()
	{
		$html = '';
		$element = $this->getElement();

		// NORMAL STYLES
		$styles = [];
		$styles['color'] = $this->getStyleColor($element->getData('owl_color'));
		$styles['background-color'] = $this->getStyleColor($element->getData('owl_background_color'));
		$html .= $this->getStyles([
			'.owl-prev',
			'.owl-next',
			'.owl-dots .owl-dot:not(.active) span'
		], $styles);

		// HOVER STYLES
		$styles = [];
		$styles['color'] = $this->getStyleColor($element->getData('owl_hover_color'));
		$styles['background-color'] = $this->getStyleColor($element->getData('owl_hover_background_color'));
		$html .= $this->getStyles([
			'.owl-prev',
			'.owl-next',
			'.owl-dots .owl-dot:not(.active) span'
		], $styles, ':hover');

		$styles = [];
		$styles['color'] = $this->getStyleColor($element->getData('owl_hover_color'));
		$styles['background-color'] = $this->getStyleColor($element->getData('owl_hover_background_color'));
		$html .= $this->getStyles([
			'.owl-dots .owl-dot:not(.active)'
		], $styles, ':hover > span');

		// ACTIVE STYLES
		$styles = [];
		$styles['color'] = $this->getStyleColor($element->getData('owl_active_color'));
		$styles['background-color'] = $this->getStyleColor($element->getData('owl_active_background_color'));
		$html .= $this->getStyles([
			'.owl-dots .owl-dot.active span',
			'.mgz-carousel .owl-dots .owl-dot.active span'
		], $styles);

		$styles = [];
		$styles['background-color'] = $this->getStyleColor($element->getData('product_background'));
		$styles['padding'] = $this->getStyleProperty($element->getData('product_padding'));
		$html .= $this->getStyles('.product-item', $styles);

		return $html;
	}

	/**
	 * @return string
	 */
	public function getLineStyles()
	{
		$html = '';
		$element = $this->getElement();

		if ($element->getData('show_line')) {
			$htmlId = '.' . $element->getHtmlId();
			$styles                     = [];
			$styles['height']           = $this->getStyleProperty($element->getData('line_width'));
			$styles['background-color'] = $this->getStyleColor($element->getData('line_color'));
			$stylesHtml = $this->parseStyles($styles);
			if ($stylesHtml) {
				$html .= $htmlId . ' .mgz-block-heading-line:before{';
				$html .= $stylesHtml;
				$html .= '}';
			}
		}

		if ($element->getData('title')) {
			$styles = [];
			$styles['color'] = $this->getStyleColor($element->getData('title_color'));
			$html .= $this->getStyles('.title', $styles);
		}

		return $html;
	}

    public function toObjectArray($items)
    {
    	$result = [];
    	if (is_array($items)) {
	    	foreach ($items as $item) {
				$result[] = new \Magento\Framework\DataObject($item);
			}
		}
    	return $result;
    }

    public function getFixedStyleProperty($type)
    {
    	$result = '';
    	switch ($type) {
    		case 'flex':
    			$result = 'display: -webkit-box;display: -webkit-flex;display: -ms-flexbox;display: flex;';
    			break;
    		
    		default:
    			# code...
    			break;
    	}
    	return $result;
    }

    public function getAdditionalStyleHtml()
    {
    	return '';
    }
}