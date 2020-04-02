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

class RecentReviews extends \Magezon\Builder\Block\Style
{
	/**
	 * @return string
	 */
	public function getAdditionalStyleHtml()
	{
		$element   = $this->getElement();
		$id        = $element->getId();
		$styleHtml = $this->getOwlCarouselStyles();

		$styleHtml .= $this->getLineStyles();

		$styles = [];
		$styles['color'] = $this->getStyleColor($element->getData('review_color'));
		$styles['background-color'] = $this->getStyleColor($element->getData('review_background_color'));
		$styleHtml .= $this->getStyles('.mgz-review-item', $styles);

		$styles = [];
		$styles['color'] = $this->getStyleColor($element->getData('review_link_color'));
		$styleHtml .= $this->getStyles('.mgz-review-item a', $styles);

		$styles = [];
		if ($element->getData('equal_height')) {
			$styleHtml .= '.' . $id . ' .owl-stage{';
			$styleHtml .= $this->getFixedStyleProperty('flex');
			$styleHtml .= '}';
		}

		if ($element->getData('review_star_color')) {
			$styles['color'] = $this->getStyleColor($element->getData('review_star_color'));
			$styleHtml .= $this->getStyles(['.rating-result > span'], $styles, ':before');
		}

		return $styleHtml;
	}
}