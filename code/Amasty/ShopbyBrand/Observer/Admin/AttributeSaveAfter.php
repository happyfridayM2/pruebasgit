<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Observer\Admin;

use Amasty\ShopbyBrand\Helper\Data as BrandHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class AttributeSaveAfter
 *
 * @package Amasty\ShopbyBrand\Observer\Admin
 */
class AttributeSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var BrandHelper
     */
    private $brandHelper;

    public function __construct(
        BrandHelper $settingHelper
    ) {
        $this->brandHelper = $settingHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->brandHelper->getBrandAttributeCode() ==
            $observer->getEvent()->getAttribute()->getAttributeCode()
        ) {
            $this->brandHelper->updateBrandOptions();
        }
    }
}
