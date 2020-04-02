<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Plugin;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\GroupedProduct\Pricing\Price\FinalPrice as MagentoFinalPrice;

class FinalPrice
{
    /**
     * @var \Amasty\Base\Model\MagentoVersion
     */
    private $magentoVersion;

    public function __construct(\Amasty\Base\Model\MagentoVersion $magentoVersion)
    {
        $this->magentoVersion = $magentoVersion;
    }

    public function aroundGetValue(MagentoFinalPrice $subject, callable $proceed)
    {
        if (version_compare($this->magentoVersion->get(), '2.1.9', '<=')) {
            return $this->getValueNew($subject);
        }

        return $proceed();
    }

    private function getValueNew($subject)
    {
        /** @var MagentoFinalPrice $subject */
        $minProduct = $subject->getMinProduct();
        return $minProduct ?
            $minProduct->getPriceInfo()->getPrice(MagentoFinalPrice::PRICE_CODE)->getValue() :
            0.00;
    }
}
