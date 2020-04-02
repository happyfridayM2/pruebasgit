<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\CatalogSearch\Block;

class Result extends \Amasty\Conf\Plugin\ListProductAbstract
{
    /**
     * @param \Magento\CatalogSearch\Block\Result|\Magento\CatalogSearch\Block\Advanced\Result $subject
     * @param $result
     * @return string
     */
    public function afterToHtml(
        $subject,
        $result
    ) {
        if ($subject->getListBlock()) {
            $result .= $this->generateFlipperConfig($subject->getListBlock());
        }

        return $result;
    }
}
