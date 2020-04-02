<?php

namespace Abbacus\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;

class CurrencyDisplayOptions implements ObserverInterface
{
    /**
     * hook to event currency_display_options_forming
     * change currency symbol position
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $baseCode = $observer->getEvent()->getBaseCode();
        $currencyOptions = $observer->getEvent()->getCurrencyOptions();

        if ($baseCode === 'EUR') { // change currency symbol position to Right with EUR
            $currencyOptions->setData('position', \Magento\Framework\Currency::RIGHT);
        }

        return $this;
    }
}