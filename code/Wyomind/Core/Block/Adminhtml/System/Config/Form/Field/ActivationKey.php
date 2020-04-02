<?php
/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Core\Block\Adminhtml\System\Config\Form\Field;

/**
 * Class Version
 */
class ActivationKey extends \Magento\Config\Block\System\Config\Form\Field
{


    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_encryptor = $encryptor;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        $ak = $this->_encryptor->decrypt($element->getValue());

        if ($ak == "") {
            $rtn = "<div class='message message-warning'>" . __("Pending") . "</div>";
        } else {
            $rtn = "<div class='message message-success'>" . $ak . "</div>";
        }

        $html = '<td class="value">';
        $html .= $rtn;


        $html .= '</td>';
        return $html;
    }


}