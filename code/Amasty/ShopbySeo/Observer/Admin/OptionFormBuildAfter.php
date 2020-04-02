<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Observer\Admin;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Helper\FilterSetting;
use Magento\Catalog\Model\Category\Attribute\Source\Page;
use Magento\Framework\Data\Form;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class OptionFormBuildAfter
 * @package Amasty\ShopbySeo\Observer\Admin
 */
class OptionFormBuildAfter implements ObserverInterface
{
    /**
     * @var Page
     */
    protected $page;

    /**
     * @var  FilterSetting
     */
    protected $filterSettingHelper;

    /**
     * @var  OptionSettingInterface
     */
    protected $model;

    /**
     * OptionFormBuildAfter constructor.
     * @param Page $page
     * @param FilterSetting $filterSettingHelper
     */
    public function __construct(Page $page, FilterSetting $filterSettingHelper)
    {
        $this->page = $page;
        $this->filterSettingHelper = $filterSettingHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Form $form */
        $form = $observer->getData('form');

        /** @var OptionSettingInterface $setting */
        $this->model = $observer->getData('setting');

        if ($this->isSeoURLEnabled()) {
            $form->getElement('url_alias')->setData(
                'note',
                __('Enable SEO URL for the attribute in order to use URL Aliases')
            );
        }
    }

    /**
     * @return bool
     */
    protected function isSeoURLEnabled()
    {
        $filterSetting = $this->filterSettingHelper->getSettingByAttributeCode($this->model->getFilterCode());
        if (!$filterSetting->getId()) {
            return false;
        }

        return $filterSetting->isSeoSignificant();
    }
}
