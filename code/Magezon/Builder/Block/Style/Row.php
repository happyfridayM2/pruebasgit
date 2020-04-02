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

namespace Magezon\Builder\Block\Style;

class Row extends \Magezon\Builder\Block\Style
{
	/**
	 * @return string
	 */
	public function getAdditionalStyleHtml()
	{
		$styleHtml = '';
		$element   = $this->getElement();
		$elements  = $element->getElements();
		$type      = $element->getData('row_type');
		$count     = count($elements);
		$gap       = (int) $element->getGap();
		if ($gap && $count) {
			$styleHtml .= '.' . $element->getHtmlId() . ' > .mgz-element-inner{';
				$styleHtml .= 'margin-left:-' . ($gap/2) . 'px !important;';
				$styleHtml .= 'margin-right:-' . ($gap/2) . 'px !important;';
			$styleHtml .= '}';
			$childSelector = $parallaxSelector = '';
			foreach ($elements as $i => $_element) {
				$childSelector .= '.' . $_element->getHtmlId();
				$parallaxSelector .= '.' . $_element->getParallaxId();
				if (isset($elements[$i+1])) {
					$childSelector .= ',';
					$parallaxSelector .= ',';
				}
			}
			$styleHtml .=  $childSelector . '{';
				$styleHtml .= 'padding:' . ($gap/2) . 'px !important;';
			$styleHtml .= '}';
			$styleHtml .=  $parallaxSelector . '{';
				$styleHtml .= 'top:' . ($gap/2) . 'px;';
				$styleHtml .= 'right:' . ($gap/2) . 'px;';
				$styleHtml .= 'bottom:' . ($gap/2) . 'px;';
				$styleHtml .= 'left:' . ($gap/2) . 'px;';
			$styleHtml .= '}';
		}

		if ($element->getData('max_width')) {
			$styleHtml .= '.' . $element->getHtmlId() . ' > .mgz-element-inner > .mgz-element-inner-content{';
				$styleHtml .= 'width:' . $element->getData('max_width') . 'px;';
				$styleHtml .= 'max-width:100%';
			$styleHtml .= '}';
		}

		return $styleHtml;
	}
}