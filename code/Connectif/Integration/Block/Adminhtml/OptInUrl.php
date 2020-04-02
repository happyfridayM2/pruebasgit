<?php

namespace Connectif\Integration\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\StoreManagerInterface;

class OptInUrl extends Field
{

    protected $_storeManager;

     public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
    }

    public function _getElementHtml(AbstractElement $element)
    {
        $optInUrl = $this->_storeManager->getStore()->getBaseUrl() . 'connectif/newsletter/optin';
        $html = '<p style="white-space: nowrap;">' . $optInUrl . '</p>';
        return $html;
    }
   
}
