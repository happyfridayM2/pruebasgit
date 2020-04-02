<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */

namespace Amasty\ShopbyBrand\Observer\Admin;

use Amasty\ShopbyBrand\Helper\Data as BrandHelper;

/**
 * Class ConfigChanged
 *
 * @package Amasty\ShopbyBrand\Observer\Admin
 */
class ConfigChanged implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var BrandHelper
     */
    private $brandHelper;

    public function __construct(BrandHelper $settingHelper)
    {
        $this->brandHelper = $settingHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->brandHelper->updateBrandOptions();
    }
}
