<?php

namespace Connectif\Integration\Block\Adminhtml;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magento\Config\Model\Config\Source\Yesno;

class MultiAccountBlock extends Select
{

    protected $_yesno;

    /**
     * Multi account Block constructor.
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        Yesno $yesno,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_yesno = $yesno;
    }

    public function _toHtml()
    {
    
        $options = $this->_yesno->toOptionArray();
        foreach ($options as $option) {
            $this->addOption($option['value'], $option['label']);
        }

        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
