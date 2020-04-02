<?php

namespace Connectif\Integration\Helper;

use Magento\Newsletter\Model\Subscriber;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Helper class for Newsletter related features.
 */
class NewsletterHelper extends AbstractHelper
{
    protected $_subscriberFactory;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        Subscriber $subscriber
    ) {
        parent::__construct($context);
        $this->_subscriber = $subscriber;
    }
    /**
     * Checks if customer is subscribed to newsletter
     *
     * @param $email
     * @return bool
     */
    public function isOptedIn($email)
    {
        $subscription = $this->_subscriber->loadByEmail($email);
        return $subscription->isSubscribed();
    }

    /**
     * Subscribe customer to newsletter
     * @param $email
     */
    public function subscribeEmail($email) {
        $subscription = $this->_subscriber->loadByEmail($email);

        if($subscription && !$subscription->isSubscribed()) {
            $subscription->setStatus(Subscriber::STATUS_SUBSCRIBED);
            $subscription->setStatusChanged(true);
            $subscription->save();
        }
    }

    /**
     * Unsubscribe customer to newsletter
     * @param $email
     */
    public function unsubscribeEmail($email) {
        $subscription = $this->_subscriber->loadByEmail($email);

        if($subscription && $subscription->isSubscribed()) {
            $subscription->setStatus(Subscriber::STATUS_UNSUBSCRIBED);
            $subscription->setStatusChanged(true);
            $subscription->save();
        }
    }
}
