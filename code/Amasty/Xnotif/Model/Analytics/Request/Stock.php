<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model\Analytics\Request;

use Magento\Catalog\Model\AbstractModel;
use Amasty\Xnotif\Model\ResourceModel\Analytics\Request\Stock as StockResource;
use Amasty\Xnotif\Api\Analytics\Data\StockInterface;

/**
 * Class Stock
 */
class Stock extends AbstractModel implements StockInterface
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(StockResource::class);
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
    public function getOrders()
    {
        return $this->_getData(StockInterface::ORDERS);
    }

    /**
     * @inheritdoc
     */
    public function setOrders($orders)
    {
        $this->setData(StockInterface::ORDERS, $orders);

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
