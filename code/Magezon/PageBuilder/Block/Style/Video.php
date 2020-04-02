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

class Video extends \Magezon\Builder\Block\Style
{
	/**
	 * @return string
	 */
	public function getAdditionalStyleHtml()
	{
		$element   = $this->getElement();
		$styleHtml = $this->getLineStyles();

		if ($element->getShowPlayIcon()) {
			$styles              = [];
			$styles['font-size'] = $this->getStyleProperty($element->getData('play_icon_size'));
			$styles['color']     = $this->getStyleColor($element->getData('play_icon_color'));
			$styleHtml .= $this->getStyles('.mgz-video-embed-play i', $styles);
		}

		if ($element->getData('video_title')) {
			$styles                = [];
			$styles['font-weight'] = $element->getData('video_title_font_weight');
			$styles['color']       = $this->getStyleColor($element->getData('video_title_color'));
			$styles['font-size']   = $this->getStyleProperty($element->getData('video_title_font_size'));
			$styles['margin-top']  = $this->getStyleProperty($this->getStyleProperty($element->getData('video_title_spacing')));
			$styleHtml .= $this->getStyles('.mgz-video-title', $styles);
		}

		if ($element->getData('video_description')) {
			$styles                = [];
			$styles['font-weight'] = $element->getData('video_description_font_weight');
			$styles['color']       = $this->getStyleColor($element->getData('video_description_color'));
			$styles['font-size']   = $this->getStyleProperty($element->getData('video_description_font_size'));
			$styles['margin-top']  = $this->getStyleProperty($this->getStyleProperty($element->getData('video_description_spacing')));
			$styleHtml .= $this->getStyles('.mgz-video-description', $styles);
		}

		if ($element->getLightbox() && $element->getLightboxWidth()) {
			$target    = '.' . $element->getHtmlId();
			$styleHtml .= $target . '-popup .mfp-content{';
				$styleHtml .= 'width: ' . $this->getStyleProperty($element->getLightboxWidth()) . ';';
				$styleHtml .= 'max-width: 100%;';
			$styleHtml .= '}';	
		}

		return $styleHtml;
	}
}