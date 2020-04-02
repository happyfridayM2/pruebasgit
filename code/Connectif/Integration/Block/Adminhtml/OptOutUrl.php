<?php

namespace Connectif\Integration\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\StoreManagerInterface;

class OptOutUrl extends Field
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
        $optOutUrl = $this->_storeManager->getStore()->getBaseUrl() . 'connectif/newsletter/optout';
        $html = '<p style="white-space: nowrap;">' . $optOutUrl . '</p>';
        return $html;
    }
   
}
