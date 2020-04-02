<?php

namespace Connectif\Integration\Block\Adminhtml;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;

class BannerBlock extends Select
{
    /**
     * Banner Block constructor.
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function _toHtml()
    {
        $options = array(
            "0" => "home",
            "1" => "category_page",
            "2" => "product_view",
            "3" => "catalog_search",
            "4" => "cart_checkout",
            "5" => "product_crosssell"
        );

        foreach ($options as $option) {
            $this->addOption($option, $option);
        }

        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
