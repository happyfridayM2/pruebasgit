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

class SingleImage extends \Magezon\Builder\Block\Style
{
	/**
	 * @return string
	 */
	public function getAdditionalStyleHtml()
	{
		$styleHtml = '';
		$element   = $this->getElement();

		$styles = [];
		if ($element->getData('image_border_width')) {
			$styles['border-width'] = $this->getStyleProperty($element->getData('image_border_width'));
			$styles['border-style'] = $element->getData('image_border_style');
			$styles['border-color'] = $this->getStyleColor($element->getData('image_border_color'));
		}
		$styles['border-radius']    = $this->getStyleProperty($element->getData('image_border_radius'));
		$styles['background-color'] = $this->getStyleColor($element->getData('image_background_color'));

		$selectors[] = '.mgz-single-image-wrapper';
		if ($element->getData('image_style') == 'mgz-box-outline') {
			$selectors[] = 'img';
		}
		$styleHtml .= $this->getStyles($selectors, $styles);

		$styles = [];
		$styles['padding'] = $this->getStyleProperty($element->getData('content_padding'));
		$styles['background-color'] = $this->getStyleColor($element->getData('content_background'));
		$styles['color'] = $this->getStyleColor($element->getData('content_color'));
		if ($element->getData('content_fullwidth')) {
			$styles['width'] = '100%';
		}
		$styles['text-align'] = $element->getData('content_align');
		$styleHtml .= $this->getStyles('.image-content', $styles);

		$styles = [];
		$styles['background-color'] = $this->getStyleColor($element->getData('content_hover_background'));
		$styles['color'] = $this->getStyleColor($element->getData('content_hover_color'));
		$styleHtml .= $this->getStyles('.mgz-single-image-wrapper:hover .image-content', $styles);

		$styles = [];
		$styles['font-size']   = $this->getStyleProperty($element->getData('title_font_size'));
		$styles['font-weight'] = $element->getData('title_font_weight');
		$styleHtml .= $this->getStyles('.image-title', $styles);

		$styles = [];
		$styles['font-size']   = $this->getStyleProperty($element->getData('description_font_size'));
		$styles['font-weight'] = $element->getData('description_font_weight');
		$styleHtml .= $this->getStyles('.image-description', $styles);

		$styles = [];
		$styles['border-radius'] = $this->getStyleProperty($element->getData('image_border_radius'));
		$styleHtml .= $this->getStyles('img', $styles);

		$styles = [];
		$styles['background-color'] = $this->getStyleColor($element->getData('overlay_color'));
		$styleHtml .= $this->getStyles('.mgz-overlay', $styles);

		$styles = [];
		$styles['background-color'] = $this->getStyleColor($element->getData('hover_overlay_color'));
		$styleHtml .= $this->getStyles('.mgz-single-image-wrapper:hover .mgz-overlay', $styles);

		return $styleHtml;
	}
}