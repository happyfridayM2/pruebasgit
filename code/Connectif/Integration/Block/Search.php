<?php

namespace Connectif\Integration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\CatalogSearch\Helper\Data as CatalogSearch;
use Magento\Framework\Escaper;
use Magento\Store\Model\ScopeInterface;
use Connectif\Integration\Helper\ConfigHelper;

class Search extends Template
{
    protected $_escaper;
    protected $_catalogSearch;
    protected $_configHelper;

    /**
     * Constructor.
     * @param Context $context the context.
     * @param array $data optional data.
     */
    public function __construct(
        Context $context,
        Escaper $escaper,
        ConfigHelper $configHelper,
        CatalogSearch $catalogSearch,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_escaper = $escaper;
        $this->_configHelper = $configHelper;
        $this->_catalogSearch = $catalogSearch;
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
     * Returns the search term.
     */
    public function getSearchTerm()
    {
        return $this->_catalogSearch->getEscapedQueryText();
    }

    public function getHtmlEscaper()
    {
        return $this->_escaper;
    }
}
