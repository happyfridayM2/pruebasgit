<?php

namespace Connectif\Integration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Category;
use Magento\Framework\Escaper;
use Magento\Store\Model\ScopeInterface;
use Connectif\Integration\Helper\ConfigHelper;

class PageCategory extends Template
{
    protected $_escaper;
    protected $_registry;
    protected $_category;
    protected $_configHelper;

    /**
     * Constructor.
     * @param Context $context the context.
     * @param array $data optional data.
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ConfigHelper $configHelper,
        Category $category,
        Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_escaper = $escaper;
        $this->_registry = $registry;
        $this->_category = $category;
        $this->_configHelper = $configHelper;
    }

    /**
     * Render HTML for the page type if the module is enabled for the current
     * store.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $isEnabled = $this->_configHelper->isEnabled(ScopeInterface::SCOPE_STORE);
        if (!$isEnabled) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Returns the page category
     *
     * @return string
     */
    public function getCurrentCategory()
    {
        $category = $this->_registry->registry('current_category');
        if (!$category) {
            return null;
        }
        $catPath = $this->getCategoryPath($category);
        return $catPath;
    }

    protected function getCategoryPath($category)
    {
        $categoryPath = '/' . $category->getName();

        if ($category->getParentId() == 1 || $category->getParentId() == null) {
            return $categoryPath;
        } else {
            $catPath = $this->getCategoryPath($this->_category->load($category->getParentId()));
            return $catPath . $categoryPath;
        }
    }

    public function getHtmlEscaper()
    {
        return $this->_escaper;
    }
}
