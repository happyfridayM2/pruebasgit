<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Helper;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Helper\OptionSetting;
use Magento\Catalog\Model\Layer;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManager;

/**
 * Class Content
 *
 * @package Amasty\ShopbyBrand\Helper
 */
class Content extends AbstractHelper
{
    const APPLIED_BRAND_VALUE = 'applied_brand_customizer_value';
    const CATEGORY_FORCE_MIXED_MODE = 'amshopby_force_mixed_mode';
    const CATEGORY_SHOPBY_IMAGE_URL = 'amshopby_category_image_url';

    /**
     * @var  Layer\Resolver
     */
    private $layerResolver;

    /**
     * @var  OptionSetting
     */
    private $optionHelper;

    /**
     * @var  StoreManager
     */
    private $storeManager;

    /**
     * OptionSettingInterface|null
     */
    private $currentBranding = null;

    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Context $context,
        Layer\Resolver $layerResolver,
        OptionSetting $optionHelper,
        StoreManager $storeManager,
        Data $helper
    ) {
        parent::__construct($context);
        $this->layerResolver = $layerResolver;
        $this->optionHelper = $optionHelper;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * Get current Brand.
     * @return null|OptionSettingInterface
     */
    public function getCurrentBranding()
    {
        if (!$this->currentBranding) {
            if ($this->checkControllerName() &&
                $this->helper->getBrandAttributeCode() &&
                ($brandValue = $this->getBrandValue()) &&
                $this->checkRootCategory()
            ) {
                $this->loadSetting($brandValue);
            } else {
                $this->currentBranding = null;
            }
        }

        return $this->currentBranding;
    }

    /**
     * @param $brandValue
     */
    private function loadSetting($brandValue)
    {
        $this->currentBranding = $this->optionHelper->getSettingByValue(
            $brandValue,
            \Amasty\ShopbyBase\Helper\FilterSetting::ATTR_PREFIX . $this->helper->getBrandAttributeCode(),
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * @return bool
     */
    private function checkControllerName()
    {
        return $this->_request->getControllerName() === 'index';
    }

    /**
     * @return mixed
     */
    private function getBrandValue()
    {
        return $this->_request->getParam($this->helper->getBrandAttributeCode());
    }

    /**
     * @return bool
     */
    protected function checkRootCategory()
    {
        $layer = $this->layerResolver->get();

        return $layer->getCurrentCategory()->getId() ==
            $layer->getCurrentStore()->getRootCategoryId();
    }
}
