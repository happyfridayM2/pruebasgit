<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Navigation;

use Magento\Framework\View\Element\Template;

/**
 * @api
 */
class JsInit extends \Magento\Framework\View\Element\Template
{

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'jsinit.phtml';

    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    private $helper;

    public function __construct(
        Template\Context $context,
        \Amasty\Shopby\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    public function collectFilters()
    {
        return (int)$this->helper->collectFilters();
    }
}
