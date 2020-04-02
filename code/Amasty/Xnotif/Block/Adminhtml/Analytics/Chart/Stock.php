<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Block\Adminhtml\Analytics\Chart;

use Magento\Backend\Block\Template;
use Amasty\Xnotif\Model\ResourceModel\Analytics\Request\Stock\CollectionFactory as AnalyticsCollectionFactory;
use Amasty\Xnotif\Model\ResourceModel\Analytics\Request\Stock\Collection as AnalyticsCollection;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\Currency\DefaultLocator;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class Stock
 */
class Stock extends Template
{
    protected $_template = 'Amasty_Xnotif::analytics/chart/stock.phtml';

    /**
     * @var AnalyticsCollectionFactory
     */
    private $stockAnalyticsFactory;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var Currency
     */
    private $defaultBaseCurrency;

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var string
     */
    private $defaultBaseCurrencyCode;

    /**
     * @var string
     */
    private $symbol = null;

    /**
     * @var null|array
     */
    private $totals;

    public function __construct(
        AnalyticsCollectionFactory $stockAnalyticsFactory,
        EncoderInterface $jsonEncoder,
        DateTime $dateTime,
        Template\Context $context,
        StoreInterface $store,
        DefaultLocator $currencyLocator,
        CurrencyFactory $currencyFactory,
        CurrencyInterface $localeCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->stockAnalyticsFactory = $stockAnalyticsFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->dateTime = $dateTime;
        $this->store = $store;
        $this->defaultBaseCurrencyCode = $currencyLocator->getDefaultCurrency($this->_request);
        $this->defaultBaseCurrency = $currencyFactory->create()->load($this->defaultBaseCurrencyCode);
        $this->localeCurrency = $localeCurrency;
    }

    /**
     * @return Phrase
     */
    public function getTitle()
    {
        return __('Back in Stock Requests');
    }

    /**
     * @return string
     */
    public function getAnalyticsData()
    {
        $analyticsConfig = [];
        $analyticsData = $this->getAnalyticsCollectionData();

        /** @var \Amasty\Xnotif\Model\Analytics\Request\Stock $analyticData */
        foreach ($analyticsData as $key => $analyticData) {
            $analyticsConfig[] = $analyticData->toArray(['subscribed', 'sent', 'orders', 'date']);
            $analyticsConfig[$key]['date'] = $this->dateTime->date('F, Y', $analyticsConfig[$key]['date']);
            $analyticsConfig[$key]['orders'] = $this->convertPrice($analyticsConfig[$key]['orders']);
        }

        return $this->jsonEncoder->encode($analyticsConfig);
    }

    /**
     * @return AnalyticsCollection
     */
    protected function getAnalyticsCollectionData()
    {
        return $this->stockAnalyticsFactory->create()
            ->groupByMonth();
    }

    /**
     * @param string $price
     *
     * @return string
     */
    private function convertPrice($price)
    {
        $price = (float)$price * $this->getRate();

        return $price;
    }

    /**
     * @return float
     */
    protected function getRate()
    {
        return $this->defaultBaseCurrency->getRate($this->defaultBaseCurrencyCode);
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        if ($this->symbol === null) {
            $this->symbol = $this->localeCurrency->getCurrency($this->defaultBaseCurrencyCode)->getSymbol();
        }

        return $this->symbol;
    }

    /**
     * @param string $field
     *
     * @return string
     * @throws \Zend_Currency_Exception
     */
    public function getTotal($field)
    {
        if (!$this->totals) {
            $this->totals = $this->getTotalRowData();
            if (isset($this->totals['orders'])) {
                $this->totals['orders'] = $this->priceOutput(
                    $this->convertPrice($this->totals['orders'])
                );
            }
        }
        $result = isset($this->totals[$field]) ? $this->totals[$field] : '';

        return $result;
    }

    /**
     * @return array
     */
    protected function getTotalRowData()
    {
        return $this->stockAnalyticsFactory->create()
            ->getTotalRow();
    }

    /**
     * @param $price
     *
     * @return string
     * @throws \Zend_Currency_Exception
     */
    private function priceOutput($price)
    {
        return $this->localeCurrency->getCurrency($this->defaultBaseCurrencyCode)->toCurrency(
            $price,
            ['symbol' => '']
        );
    }
}
