<?php

namespace Connectif\Integration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Connectif\Integration\Helper\SystemHelper;
use Magento\Store\Model\ScopeInterface;
use Connectif\Integration\Helper\ConfigHelper;

class Script extends Template
{
    protected $_configHelper;
    protected $_systemHelper;

    /**
     * Constructor.
     * @param Context $context the context.
     * @param array $data optional data.
     */
    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        SystemHelper $systemHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_configHelper = $configHelper;
        $this->_systemHelper = $systemHelper;
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
     * Returns the script meta data.
     */
    public function getScriptData()
    {
        $appConfig = $this->_systemHelper->getConnectifAppConfig();
        $storeConfig = $this->_configHelper->getCurrentStoreConfig(ScopeInterface::SCOPE_STORE);

        $scriptData = array();
        $scriptData['appConfig'] = $appConfig;
        $scriptData['client_id'] = $storeConfig['client_id'];
        return $scriptData;
    }
}
