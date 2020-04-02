<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Observer;

use Magento\Framework\Event\ObserverInterface;
use Amasty\ShopbyBase\Helper\Data;

/**
 * Class CategoryManagerInitAfter
 * @package Amasty\Shopby\Observer
 */
class CategoryManagerInitAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->coreRegistry = $registry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->coreRegistry->registry(Data::SHOPBY_CATEGORY_INDEX)) {
            $this->coreRegistry->register(Data::SHOPBY_CATEGORY_INDEX, true);
        }
    }
}
