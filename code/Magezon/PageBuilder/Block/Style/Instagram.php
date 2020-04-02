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
 * @package   Magezon_PageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\PageBuilder\Block\Style;

class Instagram extends \Magezon\Builder\Block\Style
{
	/**
	 * @return string
	 */
	public function getAdditionalStyleHtml()
	{
		$styleHtml = '';
		$element   = $this->getElement();

		if ($gap = (int)$element->getData('gap')) {
			$styles = [];
			$styles['padding'] = $this->getStyleProperty($gap / 2);
			$styleHtml .= $this->getStyles([
				'.mgz-grid-item'
			], $styles);
		}

		$styles = [];
		$styles['font-size'] = $this->getStyleProperty($element->getData('text_size'));
		$styles['color'] = $this->getStyleColor($element->getData('text_color'));
		$styleHtml .= $this->getStyles(['.item-likes', '.item-comments'], $styles);

		$styles = [];
		$styles['background'] = $this->getStyleColor($element->getData('overlay_color'));
		$styleHtml .= $this->getStyles('.item-metadata', $styles);

		$styleHtml .= $this->getLineStyles();

		return $styleHtml;
	}
}