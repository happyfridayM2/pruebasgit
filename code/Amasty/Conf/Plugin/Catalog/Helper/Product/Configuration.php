<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\Catalog\Helper\Product;

use Amasty\Base\Model\MagentoVersion;
use Magento\Catalog\Helper\Product\Configuration as MagentoConfiguration;

class Configuration
{
    /**
     * @var \Amasty\Conf\Helper\Data
     */
    private $helper;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        \Amasty\Conf\Helper\Data $helper,
        MagentoVersion $magentoVersion
    ) {
        $this->helper = $helper;
        $this->magentoVersion = $magentoVersion;
    }

    public function aroundGetFormattedOptionValue(
        MagentoConfiguration $subject,
        \Closure $proceed,
        $optionValue,
        $params = null
    ) {
        $result = $proceed($optionValue, $params);
        if ($this->helper->isShowImageSwatchOnCheckout()) {
            $optionId = isset($optionValue['option_value'])? $optionValue['option_value']: 0;
            $newLabel = $this->helper->getFormatedSwatchLabel($optionId);
            if ($newLabel) {
                $result['value'] = $newLabel;
                if (version_compare($this->magentoVersion->get(), '2.3.3', '<')) {
                    $result['full_view'] = $newLabel;
                }
            }
        }

        return  $result;
    }
}
