<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
namespace Amasty\Conf\Plugin\ConfigurableProduct\CustomerData;

use Magento\ConfigurableProduct\CustomerData\ConfigurableItem as MagentoConfigurableItem;

class ConfigurableItem
{
    /**
     * @var \Amasty\Conf\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Swatches\Model\Swatch
     */
    private $swatchAttribute;

    public function __construct(
        \Amasty\Conf\Helper\Data $helper,
        \Magento\Swatches\Model\Swatch $swatchAttribute
    ) {
        $this->helper = $helper;
        $this->swatchAttribute = $swatchAttribute;
    }

    public function afterGetItemData(MagentoConfigurableItem $subject, $item)
    {
        if ($this->helper->isShowImageSwatchOnCheckout()) {
            if (isset($item['options'])) {
                foreach ($item['options'] as $keyOption => $optionValue) {
                    $optionId = isset($optionValue['option_value']) ? $optionValue['option_value'] : 0;

                    $newLabel = $this->helper->getFormatedSwatchLabel($optionId);
                    if ($newLabel) {
                        $item['options'][$keyOption]['value'] = $newLabel;
                    }
                }
            }

        }

        return  $item;
    }
}
