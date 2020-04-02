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

namespace Magezon\PageBuilder\Block\Element;

class Categories extends \Magezon\Builder\Block\Element
{
	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $storeManager;

	/**
	 * @var \Magezon\PageBuilder\Model\Source\Categories
	 */
	protected $categories;

	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $registry;

	/**
	 * @var array
	 */
	protected $_categories;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context    
	 * @param \Magezon\PageBuilder\Model\Source\Categories     $categories 
	 * @param \Magento\Framework\Registry                      $registry   
	 * @param array                                            $data       
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magezon\PageBuilder\Model\Source\Categories $categories,
        \Magento\Framework\Registry $registry,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->storeManager = $context->getStoreManager();
		$this->categories   = $categories;
		$this->registry     = $registry;
	}

	public function prepareList($list)
	{
		$categories = $this->getElement()->getData('categories');
		foreach ($list as $category) {
			if (in_array($category['value'], $categories)) {
				$this->_categories[] = $category;
			}
			if (isset($category['optgroup']) && $category['optgroup']) {
				$this->prepareList($category['optgroup']);
			}
		}
	}

	/**
	 * @return array
	 */
	public function getCategories()
	{
		if ($this->_categories == NULL) {
			$element    = $this->getElement();
			$categories = $element->getData('categories');
			if ($categories && is_array($categories)) {
				$storeId    = $this->storeManager->getStore()->getId();
				$this->prepareList($this->categories->getCategoriesTree($storeId));
			}
			$list = [];
			if (is_array($this->_categories)) {
				foreach ($categories as $id) {
					foreach ($this->_categories as $category) {
						if ($category['id'] == $id) {
							$list[] = $category;
							break;
						}
					}
				}
			}
			$this->_categories = $list;
		}

		return $this->_categories;
	}

	/**
	 * @param  array  $categories 
	 * @param  integer $level      
	 * @return string              
	 */
	public function getCategoriesHtml($categories, $level = 0)
	{
		$element          = $this->getElement();
		$showCount        = $element->getData('show_count');
		$showHierarchical = $element->getData('show_hierarchical');
		$html = '<ul class="mgz-categories-level' . $level . '">';
		foreach ($categories as $category) {
			$classes = [];
			if ($this->isActive($category)) $classes[] = 'active';
			$_class = 'class="' . implode(' ', $classes) . '"';
			$html .= '<li ' . $_class . '>';
				$html .= '<a href="' . $category['url'] . '">';
					$html .= '<span>' . $category['name'] . '</span>';
					if ($showCount) {
						$html .= '<span>(' . $category['product_count'] . ')</span>';
					}
					if ($showHierarchical && isset($category['optgroup']) && $category['optgroup']) {
						$html .= '<span class="opener"></span>';
					}
				$html .= '</a>';
				if ($showHierarchical && isset($category['optgroup']) && $category['optgroup']) {
					$html .= $this->getCategoriesHtml($category['optgroup'], $level + 1);
				}
			$html .= '</li>';
		}
		$html .= '</ul>';
		return $html;
	}

	public function getCurrentCategory()
	{
		return $this->registry->registry('current_category');
	}

	public function isActive($category)
	{
		$currentCategory = $this->getCurrentCategory();
		if ($currentCategory && $currentCategory->getId() == $category['id']) {
			return true;
		}
		return false;
	}

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