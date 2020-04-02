<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Block\Product\TargetRule;

use Magento\TargetRule\Block\Catalog\Product\ProductList\Related as TargetRelated;

class Related extends \Amasty\Cart\Block\Product\Related
{
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Amasty\Cart\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $checkoutCart,
            $productVisibility,
            $checkoutSession,
            $moduleManager,
            $helper,
            $data
        );
        $this->setBlockType('related-rule');
    }

    /**
     * @return array
     */
    public function getAllItems()
    {
        $result = [];

        /** @var TargetRelated $targetRelateds */
        $targetRelateds = $this->getLayout()->createBlock(
            TargetRelated::class
        );
        if ($targetRelateds) {
            $relatedProducts = $targetRelateds->getAllItems();
            $result = array_slice(
                $relatedProducts,
                0,
                $this->getHelper()->getProductsQtyLimit()
            );
        }

        return $result;
    }
}
