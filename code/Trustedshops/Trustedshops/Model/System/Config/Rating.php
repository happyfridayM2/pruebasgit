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

class Rating extends \Magento\Framework\App\Config\Value
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
<script type="text/javascript"
src="//widgets.trustedshops.com/reviews/tsSticker/tsProductStickerSummary.
js"></script>
<script>
var summaryBadge = new productStickerSummary();
summaryBadge.showSummary(
{
'tsId': '%tsid%',
'sku': ['%sku%'],
'element': '#ts_product_widget',
'starColor' : '#FFDC0F',
'starSize' : '14px',
'fontSize' : '12px',
'showRating' : true,
'scrollToReviews' : false
}
);
</script>
HTML;
            $this->setValue($value);
        }

        return parent::beforeSave();
    }
}
