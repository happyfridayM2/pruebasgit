<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Amasty\ShopbyBase\Helper\FilterSetting $baseHelper */
$baseHelper = $objectManager->get(\Amasty\ShopbyBase\Helper\FilterSetting::class);
/** @var \Amasty\ShopbyBase\Model\FilterSetting $setting */
$setting =  $baseHelper->getSettingByAttributeCode('material');
$setting->setIsMultiselect(true);
$setting->setIsUseAndLogic(true);
$setting->save();
