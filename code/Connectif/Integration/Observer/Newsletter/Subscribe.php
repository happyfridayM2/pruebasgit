<?php

namespace Connectif\Integration\Observer\Newsletter;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Newsletter\Model\Subscriber;

/**
 * Class Subscribe
 * @package Connectif\Integration\Observer
 */
class Subscribe implements ObserverInterface
{
    protected $_cookieManager;
    protected $_cookieMetadataFactory;
    protected $_sessionManager;

    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager
    ) {
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;
    }

    public function execute(Observer $observer)
    {
        $model = $observer->getDataObject();
        if (!$model instanceof Subscriber) {
            return $this;
        }

        if ($model->isSubscribed() && $model->isStatusChanged()) {

            $metadata = $this->_cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setDuration(60)
                ->setPath($this->_sessionManager->getCookiePath())
                ->setDomain($this->_sessionManager->getCookieDomain());

            $this->_cookieManager->setPublicCookie(
                'connectif_newsletter_subscribed',
                $model->getData()['subscriber_email'],
                $metadata
            );
        }
    }
}
