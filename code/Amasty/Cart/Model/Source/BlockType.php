<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Model\Source;

class BlockType implements \Magento\Framework\Option\ArrayInterface
{
    const RELATED = 'related';
    const CROSSSELL = 'crosssell';
    const CMS_BLOCK = 'cms_block';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => '0',
                'label' =>__('None')
            ],
            [
                'value' => self::RELATED,
                'label' =>__('Related')
            ],
            [
                'value' => self::CROSSSELL,
                'label' =>__('Cross-sell')
            ],
            [
                'value' => self::CMS_BLOCK,
                'label' =>__('CMS Static Block')
            ]
        ];
        return $options;
    }
}
