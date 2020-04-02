<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model\ResourceModel\FilterSetting;

use Magento\Eav\Model\Entity\Attribute;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\FilterSetting;
use Amasty\ShopbyBase\Helper\FilterSetting as FilterSettingHelper;
use Amasty\ShopbySeo\Model\Source\IndexMode;
use Amasty\ShopbySeo\Model\Source\RelNofollow;
use Amasty\Shopby\Helper\Category;

/**
 * FilterSetting CollectionExtended
 */
class CollectionExtended extends Collection
{
    /**
     * @var FilterSettingHelper
     */
    private $filterSettingHelper;

    /**
     * @var array
     */
    private $filtersData = [];

    public function __construct(
        FilterSettingHelper $filterSettingHelper,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->filterSettingHelper = $filterSettingHelper;
    }

    /**
     * Disable saving data in objects
     *
     * @return null
     */
    public function getData()
    {
        parent::getData();

        return null;
    }

    /**
     * @return $this
     */
    protected function _afterLoadData()
    {
        foreach ($this->_data as $filterData) {
            $this->filtersData[$filterData[FilterSettingInterface::FILTER_CODE]] = $filterData;
        }

        return $this;
    }

    /**
     * @param $filterCode
     *
     * @return FilterSetting|null
     */
    public function getItemByCode($filterCode)
    {
        $this->load();

        $filterSetting = null;
        if (isset($this->_items[$filterCode])) {
            $filterSetting = $this->_items[$filterCode];
        } else {
            $filterSetting = $this->getNewEmptyItem();

            if (isset($this->filtersData[$filterCode])) {
                $filterSetting->addData($this->filtersData[$filterCode]);
            } else {
                $filterSetting->setFilterCode($filterCode);
                $filterSetting->addData($this->getDefaultData($filterCode));
            }
            $filterSetting->addData($this->getAdditionalData($filterCode));

            $this->_items[$filterCode] = $filterSetting;
        }

        return $filterSetting;
    }

    /**
     * @return FilterSetting
     */
    public function getNewEmptyItem()
    {
        /** @var FilterSetting $newItem */
        $newItem = parent::getNewEmptyItem();

        $newItem->setIndexMode(IndexMode::MODE_NEVER);
        $newItem->setFollowMode(IndexMode::MODE_NEVER);
        $newItem->setRelNofollow(RelNofollow::MODE_AUTO);

        return $newItem;
    }

    /**
     * @param string $filterCode
     *
     * @return array
     */
    private function getDefaultData($filterCode)
    {
        $data = [];

        switch ($filterCode) {
            case 'stock':
            case 'rating':
            case 'am_is_new':
            case 'am_on_sale':
                $data[FilterSettingInterface::FILTER_SETTING_ID] = $filterCode;
                $data[FilterSettingInterface::DISPLAY_MODE] =
                    $this->filterSettingHelper->getConfig($filterCode, 'display_mode');
                $data[FilterSettingInterface::FILTER_CODE] = $filterCode;
                $data[FilterSettingInterface::EXPAND_VALUE] =
                    $this->filterSettingHelper->getConfig($filterCode, 'is_expanded');
                $data[FilterSettingInterface::TOOLTIP] = $this->filterSettingHelper->getConfig($filterCode, 'tooltip');
                $data[FilterSettingInterface::BLOCK_POSITION] =
                    $this->filterSettingHelper->getConfig($filterCode, 'block_position');
                break;
        }

        return $data;
    }

    /**
     * @param string $filterCode
     *
     * @return array
     */
    private function getAdditionalData($filterCode)
    {
        $data = [];

        switch ($filterCode) {
            case FilterSettingHelper::ATTR_PREFIX . Category::ATTRIBUTE_CODE:
                $data = $this->filterSettingHelper->getCustomDataForCategoryFilter();
                break;
        }

        return $data;
    }

    /**
     * @param Attribute $attributeModel
     *
     * @return FilterSetting|null
     */
    public function getItemByAttribute($attributeModel)
    {
        $filterCode = FilterSettingHelper::ATTR_PREFIX . $attributeModel->getAttributeCode();
        $filterSetting = $this->getItemByCode($filterCode);
        if ($filterSetting && !$filterSetting->getAttributeModel()) {
            $filterSetting->setAttributeModel($attributeModel);
        }

        return $filterSetting;
    }
}
