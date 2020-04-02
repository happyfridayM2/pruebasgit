<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Block\Html\Link;

/**
 * Class Current
 */
class Current extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Amasty\Xnotif\Helper\Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Amasty\Xnotif\Helper\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->config = $config;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->config->allowForCurrentCustomerGroup('price')) {
            return '';
        }

        return parent::_toHtml();
    }
}
