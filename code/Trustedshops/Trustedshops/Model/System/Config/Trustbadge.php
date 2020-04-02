<?php
/**
 * @category  Trustedshops
 * @package   Trustedshops\Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */

namespace Trustedshops\Trustedshops\Model\System\Config;

use Magento\Framework\App\ObjectManager;

class Trustbadge extends \Magento\Framework\App\Config\Value
{
    /**
     * disallow saving of empty expert code
     *
     * @return mixed
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $value = trim($value);
        if (empty($value)) {
            $value = <<<HTML
<script type="text/javascript">
(function () {
var _tsid = '%tsid%';
_tsConfig = {
'yOffset': '0',
'variant': 'reviews',
'customElementId': '',
'trustcardDirection': '',
'customBadgeWidth': '',
'customBadgeHeight': '',
'disableResponsive': 'false',
'disableTrustbadge': 'false',
'trustCardTrigger': 'mouseenter',
'customCheckoutElementId': ''
};
var _ts = document.createElement('script');
_ts.type = 'text/javascript';
_ts.charset = 'utf-8';
_ts.async = true;
_ts.src = '//widgets.trustedshops.com/js/' + _tsid + '.js';
var __ts = document.getElementsByTagName('script')[0];
__ts.parentNode.insertBefore(_ts, __ts);
})();
</script>
HTML;
            $this->setValue($value);
        }

        return parent::beforeSave();
    }
}
