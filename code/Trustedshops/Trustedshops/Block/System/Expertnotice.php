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

class Expertnotice extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        // build the base html structure
        $noticeHtml = '<br/>';
        $noticeHtml .= '<div>';
        $noticeHtml .= '	<span>%s</span>';
        $noticeHtml .= '	<ul style="list-style: disc; margin-left: 20px;">';
        $noticeHtml .= '		<li>%s</li>';
        $noticeHtml .= '		<li>%s</li>';
        $noticeHtml .= '		<li>%s</li>';
        $noticeHtml .= '	</ul>';
        $noticeHtml .= '	<span>%s</span>';
        $noticeHtml .= '</div>';

        // create the language specific links
        $noticeLinkHtml = __('Learn more about %s options and %s configuration.');
        $linkTrustbadge = __('http://www.trustedshops.co.uk/support/trustbadge/trustbadge-custom/');
        $linkConfiguration = __('http://www.trustedshops.co.uk/support/product-reviews/');
        $noticeLinkHtml = sprintf(
            $noticeLinkHtml,
            '<a target="_blank" href="' . $linkTrustbadge . '">' . __('Trustbadge') . '</a>',
            '<a target="_blank" href="' . $linkConfiguration . '">' . __('Product Reviews') . '</a>'
        );

        // put everything together
        $notice = sprintf(
            $noticeHtml,
            __('Use additional options to customize your Trusted Shops Integration or use the latest code version here. E.g.:'),
            __('Place your Trustbadge wherever you want'),
            __('Deactivate mobile use'),
            __('Jump from your Product Reviews stars directly to your Product Reviews'),
            $noticeLinkHtml
        );

        return sprintf(
            '<tr id="row_%s"></td><td colspan="5" class="value"><div id="%s">%s</div></td></tr>',
            $element->getHtmlId(),
            $element->getHtmlId(),
            nl2br($notice)
        );
    }
}
