<?php

namespace Connectif\Integration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Connectif\Integration\Helper\BannerHelper;
use Magento\Store\Model\ScopeInterface;
use Connectif\Integration\Helper\ConfigHelper;

class Banner extends Template
{
    protected $_bannerHelper;
    protected $_configHelper;

    /**
     * Constructor.
     * @param Context $context the context.
     * @param array $data optional data.
     */
    public function __construct(
        Context $context,
        BannerHelper $bannerHelper,
        ConfigHelper $configHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_bannerHelper = $bannerHelper;
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
     * Return banner ids for block name
     * @return array
     */
    public function getBannerIds($blockName)
    {
        return $this->_bannerHelper->getBannerIds($blockName);
    }
}
