<?php
/**
 * @category  Trustedshops
 * @package   Trustedshops\Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */

namespace Trustedshops\Trustedshops\Block\System;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Intro extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        $imageHtml = $this->getImageHtml();
        $buttonHtml = $this->getButtonHtml();

        $html = "<div>";
        $html .= "<div>" . $imageHtml . "</div>";
        $html .= "<div>" . $buttonHtml . "</div>";
        $html .= "</div>";

        return $html;
    }

    /**
     * generate html for promotion image
     *
     * @return string
     */
    public function getImageHtml()
    {
        $imageFilename = __('trustedshops_en.jpg');
        $imageUrl = $this->getViewFileUrl('Trustedshops_Trustedshops::images/' . $imageFilename);
        $imageContent = '<img style="display:block; margin: 20px auto;" title="Trusted Shops" alt="Trusted Shops" src="' . $imageUrl . '" />';
        return $imageContent;
    }

    /**
     * generate html for register new account button
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $trustedShopsLink = __("http://www.trustbadge.com/en/pricing/")
            . '?utm_source=magento2&utm_medium=software-app&utm_content=marketing-page&utm_campaign=magento2-app';

        $buttonText = __("Get your account");
        $memberLink = "window.open('" . $trustedShopsLink . "'); return false;";
        $buttonContent = '<button style="display:block; margin: 20px auto;" onclick="' . $memberLink . '"><span><span><span>' . $buttonText . '</span></span></span></button>';
        return $buttonContent;
    }
}
