<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class AdminNotify
 */
class AdminNotify extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Xnotif::notifications/subscriptions.phtml';

    /**
     * @var null|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $subscriptionCollection = null;

    /**
     * @return null|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getSubscriptionCollection()
    {
        return $this->subscriptionCollection;
    }

    /**
     * @param null|\Magento\Catalog\Model\ResourceModel\Product\Collection $subscriptionCollection
     * @return AdminNotify
     */
    public function setSubscriptionCollection($subscriptionCollection)
    {
        $this->subscriptionCollection = $subscriptionCollection;

        return $this;
    }
}
