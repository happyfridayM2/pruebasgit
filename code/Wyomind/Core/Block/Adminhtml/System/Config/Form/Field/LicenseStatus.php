<?php
/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Core\Block\Adminhtml\System\Config\Form\Field;

/**
 * Class Version
 */
class LicenseStatus extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Wyomind\Core\Helper\Config
     */
    protected $_configHelper = null;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Wyomind\Core\Helper\Config $configHelper
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Wyomind\Core\Helper\Data $configHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_configHelper = $configHelper;
        $this->_encryptor = $encryptor;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {


        $extensionName = str_replace("_license_license_status", "", $element->getHtmlId());
        $extensionNameSpace = "Wyomind_" . str_replace(" ", "", $this->_configHelper->getStoreConfig($extensionName . "/license/extension_label"));

        $ak = $this->_configHelper->getStoreConfigUncrypted($extensionName . "/license/activation_key");
        $lc = $this->_configHelper->getStoreConfigUncrypted($extensionName . "/license/activation_code");

        $valid = $ak != "" && $lc != "";
        $status = "";
        if (!$valid) {
            if ($ak != "" && $lc == "") {
                $status = __("Invalidated");
            } else {
                $status = __("Pending");
                $ak = htmlentities("<activation key>");
            }

            $msg = "Please run bin/magento wyomind:license:activate " . $extensionNameSpace . " " . $ak;
            $rtn = "<div class='message message-danger'>" . $status . "";
            $rtn .= "<pre>$msg</pre></div>";
        } else {
            $rtn = "<div class='message message-success'>" . __("Registered") . "</div>";
        }

        $html = '<td class="value">';
        $html .= $rtn;
        $html .= '</td>';
        return $html;
    }


}