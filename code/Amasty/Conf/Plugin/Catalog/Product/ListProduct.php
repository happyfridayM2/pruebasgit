<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\Catalog\Product;

class ListProduct extends \Amasty\Conf\Plugin\ListProductAbstract
{
    /**
     * @param \Magento\Catalog\Block\Product\ListProduct $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(
        $subject,
        $result
    ) {
        $result .= $this->generateFlipperConfig($subject);

        return $result;
    }
}
