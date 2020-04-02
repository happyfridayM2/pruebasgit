<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
namespace Amasty\Conf\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\Cart as MagentoCart;

class Cart
{
    /**
     * @var \Amasty\Conf\Helper\Data
     */
    private $helper;

    public function __construct(
        \Amasty\Conf\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function afterGetSectionData(MagentoCart $subject, $result)
    {
        if ($this->helper->isShowImageSwatchOnCheckout()) {
            $items = $result['items'];
            foreach ($items as $key => $item) {
                if (isset($item['options'])) {
                    foreach ($item['options'] as $keyOption => $optionValue) {
                        $optionId = isset($optionValue['option_value']) ? $optionValue['option_value'] : 0;
                        $newLabel = $this->helper->getFormatedSwatchLabel($optionId);
                        if ($newLabel) {
                            $items[$key]['options'][$keyOption]['value'] = [$newLabel];
                        }
                    }
                }

            }

            $result['items'] = $items;
        }

        return  $result;
    }
}
