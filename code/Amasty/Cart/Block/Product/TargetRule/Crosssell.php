<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Block\Product\TargetRule;

use Magento\TargetRule\Block\Checkout\Cart\Crosssell as TargetCrosssell;

class Crosssell extends \Amasty\Cart\Block\Product\Crosssell
{
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Amasty\Cart\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $helper, $data);
        $this->setBlockType('crosssell-rule');
    }

    /**
     * @return array
     */
    public function getAllItems()
    {
        $result = [];

        /** @var TargetCrosssell $targetCrosssells */
        $targetCrosssells = $this->getLayout()->createBlock(
            TargetCrosssell::class
        );
        if ($targetCrosssells) {
            $crosssellProducts = $targetCrosssells->getItemCollection();
            $result = array_slice(
                $crosssellProducts,
                0,
                $this->getHelper()->getProductsQtyLimit()
            );
        }

        return $result;
    }
}
