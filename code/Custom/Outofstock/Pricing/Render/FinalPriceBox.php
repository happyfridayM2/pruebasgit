<?php
namespace Custom\Outofstock\Pricing\Render;

use Magento\Msrp\Pricing\Price\MsrpPrice;
use Magento\Framework\Pricing\Render\PriceBox as BasePriceBox;

class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{
    protected function _toHtml()
    {
        $result = parent::_toHtml();

        if(!$result) {
            $result = BasePriceBox::_toHtml();
            try {
                /** @var MsrpPrice $msrpPriceType */
                $msrpPriceType = $this->getSaleableItem()->getPriceInfo()->getPrice('msrp_price');
            } catch (\InvalidArgumentException $e) {
                $this->_logger->critical($e);
                return $this->wrapResult($result);
            }

            //Renders MSRP in case it is enabled
            $product = $this->getSaleableItem();
            if ($msrpPriceType->canApplyMsrp($product) && $msrpPriceType->isMinimalPriceLessMsrp($product)) {
                /** @var BasePriceBox $msrpBlock */
                $msrpBlock = $this->rendererPool->createPriceRender(
                    MsrpPrice::PRICE_CODE,
                    $this->getSaleableItem(),
                    [
                        'real_price_html' => $result,
                        'zone' => $this->getZone(),
                    ]
                );
                $result = $msrpBlock->toHtml();
            }

            return $this->wrapResult($result);
        }

        return $result;
    }
}