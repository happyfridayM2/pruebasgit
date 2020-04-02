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

class SingleProduct extends \Magezon\Builder\Block\Style
{
	/**
	 * @return string
	 */
	public function getAdditionalStyleHtml()
	{
		$element                = $this->getElement();
		$styleHtml              = $this->getLineStyles();
		$styles                 = [];
		$styles['border-color'] = $this->getStyleColor($element->getData('border_hover_color'), true);
		$_stylesHtml            = $this->parseStyles($styles);
		if ($_stylesHtml) {
			$styleHtml .= '.' . $this->getElement()->getHtmlId() . ' .product-item-info:hover{';
				$styleHtml .= $_stylesHtml;
			$styleHtml .= '}';
		}	
		return $styleHtml;
	}
}