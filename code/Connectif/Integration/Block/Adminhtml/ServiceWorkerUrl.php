<?php

namespace Connectif\Integration\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Asset\Repository;

class ServiceWorkerUrl extends Field
{

    protected $_assetRepository;

     public function __construct(
        Context $context,
        Repository $assetRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_assetRepository = $assetRepository;
    }

    public function _getElementHtml(AbstractElement $element)
    {
        $params = array();
        $params['area'] = 'frontend';
        $serviceWorkerUrl = $this->_assetRepository->getUrlWithParams('Connectif_Integration::service-worker.js', $params);
        $html = '<p style="white-space: nowrap;">' . $serviceWorkerUrl . '</p>';
        return $html;
    }
   
}
