<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Model\Config\Source;

use Magento\Eav\Model\Config as EavConfig;

/**
 * Class Attribute
 * Source model for attribute field on Custom Page Form
 */
class Attribute extends \Amasty\ShopbyBase\Model\Source\Attribute
{
    public function __construct(
        EavConfig $eavConfig
    ) {
        parent::__construct($eavConfig);
        $priceAttribute = $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'price');
        $this->skipAttributeId($priceAttribute->getAttributeId());
    }
}
