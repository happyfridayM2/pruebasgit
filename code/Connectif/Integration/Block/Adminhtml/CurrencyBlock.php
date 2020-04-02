<?php

namespace Connectif\Integration\Block\Adminhtml;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magento\Config\Model\Config\Source\Locale\Currency;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\Currency as CurrencyModel;

class CurrencyBlock extends Select
{

    protected $_currency;
    protected $_storeManager;

    /**
     * Currency Block constructor.
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        Currency $currency,
        StoreManagerInterface $storeManager,
        CurrencyModel $currencyModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_currency = $currency;
        $this->_storeManager = $storeManager;
        $this->_currenciesModel = $currencyModel;
    }

    public function _toHtml()
    {
        $currencies_array = $this->_currenciesModel->getConfigAllowCurrencies();
        if ($currencies_array[0] == '') {
            $currencies_array[] = $this->_storeManager->getStore()->getCurrentCurrency();
        }

        $options = $this->_currency->toOptionArray();
        foreach ($options as $option) {
            if (array_search($option['value'], $currencies_array) !== false) {
                $this->addOption($option['value'], $option['label']);
            }
        }

        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
