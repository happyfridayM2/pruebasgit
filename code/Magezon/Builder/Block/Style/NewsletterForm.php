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

class NewsletterForm extends \Magezon\Builder\Block\Style
{
	/**
	 * @return string
	 */
	public function getAdditionalStyleHtml()
	{
		$styleHtml = '';
		$element = $this->getElement();
		$styles['max-width'] = $this->getStyleProperty($element->getData('form_width'), true);
		if ($this->getStyles('.mgz-element-inner', $styles)) {
			$styleHtml = $this->getStyles('.newsletter', $styles);
		}
		if ($element->getData('title')) {
			$styles                  = [];
			$styles['color']         = $this->getStyleColor($element->getData('title_color'));
			$styles['margin-bottom'] = $this->getStyleProperty($element->getData('title_spacing'));
			$styles['font-size']     = $this->getStyleProperty($element->getData('title_font_size'));
			$styles['font-weight']   = $element->getData('title_font_weight');
			$styleHtml .= $this->getStyles('.newsletter-title', $styles);
		}
		return $styleHtml;
	}
}