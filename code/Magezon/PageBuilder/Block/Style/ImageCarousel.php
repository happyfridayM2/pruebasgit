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

class ImageCarousel extends \Magezon\Builder\Block\Style
{
	/**
	 * @return string
	 */
	public function getHtmlId()
	{
		return '.mgz-element.' . $this->getElement()->getHtmlId() . ' .mgz-carousel';
	}

	/**
	 * @return string
	 */
	public function getAdditionalStyleHtml()
	{
		$element   = $this->getElement();
		$styleHtml = $this->getOwlCarouselStyles();
		$styleHtml .= $this->getLineStyles();

		$styles = [];
		$styles['padding'] = $this->getStyleProperty($element->getData('content_padding'));
		$styles['background-color'] = $this->getStyleColor($element->getData('content_background'));
		$styles['color'] = $this->getStyleColor($element->getData('content_color'));
		if ($element->getData('content_fullwidth')) {
			$styles['width'] = '100%';
		}
		$styleHtml .= $this->getStyles('.item-content', $styles);

		$styles = [];
		$styles['font-size']   = $this->getStyleProperty($element->getData('title_font_size'));
		$styles['font-weight'] = $element->getData('title_font_weight');
		$styleHtml .= $this->getStyles('.item-title', $styles);

		$styles = [];
		$styles['font-size']   = $this->getStyleProperty($element->getData('description_font_size'));
		$styles['font-weight'] = $element->getData('description_font_weight');
		$styleHtml .= $this->getStyles('.item-description', $styles);

		$styles = [];
		$styles['background-color'] = $this->getStyleColor($element->getData('overlay_color'));
		$styleHtml .= $this->getStyles('.mgz-overlay', $styles);

		$styles = [];
		$styles['border-radius'] = $this->getStyleProperty($element->getData('image_border_radius'));
		if ($element->getData('image_border_style') && $element->getData('image_border_width') && $element->getData('image_border_color')) {
			$styles['border-style'] = $element->getData('image_border_style');
			$styles['border-width'] = $this->getStyleProperty($element->getData('image_border_width'));
			$styles['border-color'] = $this->getStyleColor($element->getData('image_border_color'));
		}
		$styleHtml .= $this->getStyles('.owl-item-image', $styles);

		return $styleHtml;
	}
}