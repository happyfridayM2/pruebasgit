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

class Notice extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        $comment = $element->getComment();
        // check if comments have a parameter
        if (strpos($comment, '%s') !== false) {
            // translate parameter
            $comment = sprintf(
                $comment,
                __($element->getOriginalData('comment_param'))
            );
        }

        return sprintf(
            '<tr id="row_%s"></td><td colspan="5" class="value"><br/><span id="%s">%s</span></td></tr>',
            $element->getHtmlId(),
            $element->getHtmlId(),
            nl2br($comment)
        );
    }
}
