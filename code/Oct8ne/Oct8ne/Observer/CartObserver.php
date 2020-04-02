<?php

namespace Oct8ne\Oct8ne\Observer;

/**
 * Created by PhpStorm.
 * User: migue
 * Date: 14/06/2017
 * Time: 17:16
 */

use \Magento\Framework\Event\ObserverInterface;
use \Oct8ne\Oct8ne\Model\Oct8neHistoryFactory;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Framework\Stdlib\CookieManagerInterface;


class CartObserver implements ObserverInterface
{

    /**
     * @var Oct8neHistoryFactory
     */
    protected $_oct8ne_History;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var CookieManagerInterface
     */
    protected $_cookieManager;


    /**
     * CartObserver constructor.
     * @param Oct8neHistoryFactory $oct8neHistory
     * @param CheckoutSession $checkoutSession
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(Oct8neHistoryFactory $oct8neHistory, CheckoutSession $checkoutSession, CookieManagerInterface $cookieManager)
    {

        $this->_oct8ne_History = $oct8neHistory;

        $this->_checkoutSession = $checkoutSession;

        $this->_cookieManager = $cookieManager;

    }

    /**
     * Observador
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $history = $this->_oct8ne_History->create();

        $quoteId = $this->_checkoutSession->getQuote()->getId();

        $existorId = $history->getResource()->existHistoryByCartId((int)$quoteId);

        if (!$existorId) {

            $cookie = $this->_cookieManager->getCookie("oct8ne-session");

            if (isset($cookie) && !empty($cookie)) {

                $history->setCartId($quoteId);
                $history->setCookie($cookie);
                $history->setCreationTime(gmdate('Y-m-d H:i:s'));
                $history->setUpdateTime(gmdate('Y-m-d H:i:s'));
                $history->getResource()->save($history);
            }

        } else {

            $history->getResource()->load($history, $existorId, 'id_oct8nehistory');
            $history->setUpdateTime(gmdate('Y-m-d H:i:s'));
            $history->getResource()->save($history);

        }
    }
}