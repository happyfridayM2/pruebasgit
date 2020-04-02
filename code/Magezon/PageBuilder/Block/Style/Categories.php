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

class Categories extends \Magezon\Builder\Block\Style\Button
{
	/**
	 * @return string
	 */
	public function getAdditionalStyleHtml()
	{
		$styleHtml = parent::getAdditionalStyleHtml();
		$element = $this->getElement();

		if ($element->getData('categories')) {
			$styles = [];
			$styles['color'] = $this->getStyleColor($element->getData('link_color'));
			$styles['font-size'] = $this->getStyleProperty($element->getData('link_font_size'));
			$styles['font-weight'] = $element->getData('link_font_weight');
			$styleHtml .= $this->getStyles('.mgz-element-categories-list a', $styles);

			$styles = [];
			$styles['color'] = $this->getStyleColor($element->getData('link_hover_color'));
			$styleHtml .= $this->getStyles(['.mgz-element-categories-list a:hover', '.mgz-element-categories-list li.active > a'], $styles, '');

			$styles = [];
			$styles['border-bottom-width'] = $this->getStyleProperty($element->getData('link_border_width'));
			$styles['border-bottom-color'] = $this->getStyleColor($element->getData('link_border_color'));
			$styleHtml .= $this->getStyles('.mgz-element-categories-list li', $styles);
		}

		$styleHtml .= $this->getLineStyles();

		return $styleHtml;
	}
}