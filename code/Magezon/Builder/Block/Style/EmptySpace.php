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

class EmptySpace extends \Magezon\Builder\Block\Style
{
	/**
	 * @return string
	 */
	public function getAdditionalStyleHtml()
	{
		$element = $this->getElement();
		$styles['height'] = $this->getStyleProperty($element->getData('height'));
		$styleHtml = $this->getStyles('.mgz-element-empty-space', $styles);

		$styles = [];
		$styles['height'] = $this->getStyleProperty($element->getData('lg_height'));
		if ($lgStyleHtml = $this->parseStyles($styles)) {
			$styleHtml .= '@media (max-width: 1199px) {';
			$styleHtml .= $this->getStyles('.mgz-element-empty-space', $styles);
			$styleHtml .= '}';
		}

		$styles = [];
		$styles['height'] = $this->getStyleProperty($element->getData('md_height'));
		if ($mdStyleHtml = $this->parseStyles($styles)) {
			$styleHtml .= '@media (max-width: 991px) {';
			$styleHtml .= $this->getStyles('.mgz-element-empty-space', $styles);
			$styleHtml .= '}';
		}

		$styles = [];
		$styles['height'] = $this->getStyleProperty($element->getData('sm_height'));
		if ($smStyleHtml = $this->parseStyles($styles)) {
			$styleHtml .= '@media (max-width: 767px) {';
			$styleHtml .= $this->getStyles('.mgz-element-empty-space', $styles);
			$styleHtml .= '}';
		}

		$styles = [];
		$styles['height'] = $this->getStyleProperty($element->getData('xs_height'));
		if ($xsStyleHtml = $this->parseStyles($styles)) {
			$styleHtml .= '@media (max-width: 575px) {';
			$styleHtml .= $this->getStyles('.mgz-element-empty-space', $styles);
			$styleHtml .= '}';
		}

		return $styleHtml;
	}
}