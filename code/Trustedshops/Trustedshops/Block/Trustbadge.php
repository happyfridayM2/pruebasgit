<?php
/**
 * @category  Trustedshops
 * @package   Trustedshops\Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */

namespace Trustedshops\Trustedshops\Block;

use Trustedshops\Trustedshops\Model\System\Variant;

class Trustbadge extends Base
{
    /**
     * get the y-offset
     * must be between 0 and 250
     *
     * @return int|number
     */
    public function getOffset()
    {
        $offset = abs($this->getConfig('offset', 'trustbadge'));
        return ($offset > 250) ? 250 : $offset;
    }

    /**
     * get the selected trustbadge variant
     *
     * @return string
     */
    public function getVariant()
    {
        $isHidden = $this->getDisplayTrustbadge();
        if ($isHidden === "true") {
            return Variant::VARIANT_REVIEWS;
        }
        return $this->getConfig('variant', 'trustbadge');
    }

    /**
     * check if we should display the trustbadge
     *
     * @return string|bool
     */
    public function getDisplayTrustbadge()
    {
        return ($this->getConfig('variant', 'trustbadge') == Variant::VARIANT_HIDE)
            ? 'true'
            : 'false';
    }

    /**
     * get the expert trustbadge code
     * and replace variables
     *
     * @return string
     */
    public function getCode()
    {
        $expertCode = $this->getConfig('code', 'trustbadge');
        return $this->replaceVariables($expertCode);
    }
}
