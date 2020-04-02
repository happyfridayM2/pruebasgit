<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class CategoryFlatLoadNodesBefore
 * @package Amasty\Shopby\Observer
 */
class CategoryFlatLoadNodesBefore implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Zend_Db_Select $select
         */
        $select = $observer->getEvent()->getSelect();
        $select->columns('main_table.thumbnail');
    }
}
