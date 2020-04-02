<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Source;

/**
 * Class StockFilterSource
 * @package Amasty\Shopby\Model\Source
 */
class StockFilterSource implements \Magento\Framework\Option\ArrayInterface
{
    const STOCK_STATUS = 'stock_status';
    const QTY = 'qty';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::STOCK_STATUS,
                'label' => __('Disabled stock status')
            ],
            [
                'value' => self::QTY,
                'label' => __('Quantity threshold')
            ]
        ];
    }
}
