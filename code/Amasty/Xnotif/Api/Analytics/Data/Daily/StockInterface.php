<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Api\Analytics\Data\Daily;

interface StockInterface
{
    const MAIN_TABLE = 'amasty_stock_analytics_temp';

    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const SUBSCRIBED = 'subscribed';
    const SENT = 'sent';
    const DATE = 'date';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return StockInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getSubscribed();

    /**
     * @param int $subscribed
     *
     * @return StockInterface
     */
    public function setSubscribed($subscribed);

    /**
     * @return int
     */
    public function getSent();

    /**
     * @param int $sent
     *
     * @return StockInterface
     */
    public function setSent($sent);

    /**
     * @return string
     */
    public function getDate();

    /**
     * @param string $date
     *
     * @return StockInterface
     */
    public function setDate($date);
}
