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

class Review extends \Magento\Framework\App\Config\Value
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
_tsProductReviewsConfig = {
tsid: '%tsid%',
sku: ['%sku%'],
variant: 'productreviews',
borderColor: '#0DBEDC',
locale: 'de_DE',
backgroundColor: '#ffffff',
starColor: '#FFDC0F',
richSnippets: 'on',
starSize: '15px',
ratingSummary: 'false',
maxHeight: '1200px',
'element': '#ts_product_sticker',
hideEmptySticker: 'false',
introtext: '' /* optional */
};
var scripts = document.getElementsByTagName('SCRIPT'),
me = scripts[scripts.length - 1];
var _ts = document.createElement('SCRIPT');
_ts.type = 'text/javascript';
_ts.async = true;
_ts.charset = 'utf-8';
_ts.src
='//widgets.trustedshops.com/reviews/tsSticker/tsProductSticker.js';
me.parentNode.insertBefore(_ts, me);
_tsProductReviewsConfig.script = _ts;
</script>
HTML;
            $this->setValue($value);
        }

        return parent::beforeSave();
    }
}
