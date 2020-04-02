<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model\Analytics\Request\Daily;

use Magento\Catalog\Model\AbstractModel;
use Amasty\Xnotif\Model\ResourceModel\Analytics\Request\Daily\Stock as StockResource;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Amasty\Xnotif\Api\Analytics\Data\Daily\StockInterface;

/**
 * Class Stock
 */
class Stock extends AbstractModel implements StockInterface
{
    /**
     * @var null|string
     */
    private $currentDate = null;

    protected function _construct()
    {
        parent::_construct();
        $this->_init(StockResource::class);
    }

    /**
     * @param null $input
     */
    private function initDate($input = null)
    {
        /** @var DateTime $dateTime */
        $dateTime = $this->getData('dateTime');
        if ($dateTime) {
            $this->currentDate = $dateTime->gmtDate('Y-m-d', $input);
        }
    }

    /**
     * @return $this
     */
    public function loadByDate()
    {
        /** @var StockResource $resourceModel */
        $resourceModel = $this->getData('resourceModel');
        if ($resourceModel) {
            $resourceModel->load($this, $this->currentDate, 'date');
        }

        return $this;
    }

    /**
     * @return Stock
     */
    public function loadCurrent()
    {
        $this->initDate();

        return $this->loadByDate();
    }

    /**
     * @return Stock
     */
    public function loadPrevious()
    {
        $this->initDate('-1 days');

        return $this->loadByDate();
    }

    public function updateDate()
    {
        $this->setDate($this->currentDate);
    }

    /**
     * @inheritdoc
     */
    public function getSubscribed()
    {
        return $this->_getData(StockInterface::SUBSCRIBED);
    }

    /**
     * @inheritdoc
     */
    public function setSubscribed($subscribed)
    {
        $this->setData(StockInterface::SUBSCRIBED, $subscribed);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSent()
    {
        return $this->_getData(StockInterface::SENT);
    }

    /**
     * @inheritdoc
     */
    public function setSent($sent)
    {
        $this->setData(StockInterface::SENT, $sent);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDate()
    {
        return $this->_getData(StockInterface::DATE);
    }

    /**
     * @inheritdoc
     */
    public function setDate($date)
    {
        $this->setData(StockInterface::DATE, $date);

        return $this;
    }
}
