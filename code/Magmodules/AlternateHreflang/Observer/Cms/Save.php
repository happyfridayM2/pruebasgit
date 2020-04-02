<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Observer\Cms;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class Save
 *
 * @package Magmodules\AlternateHreflang\Observer\Cms
 */
class Save implements ObserverInterface
{

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $model = $observer->getData('page');
        $data = $model->getData();
        if (!empty($data['alternate_category_new'])) {
            $category = strtolower(trim($data['alternate_category_new']));
            $model->setData('alternate_category', $category);
        }
        if (!empty($data['alternate_category']) && empty($data['alternate_category_new'])) {
            $category = strtolower(trim($data['alternate_category']));
            if ($category == 'alternate_category_new') {
                $model->setData('', $category);
            }
        }
    }
}
