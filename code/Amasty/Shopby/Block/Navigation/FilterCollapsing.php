<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Navigation;

use Amasty\Shopby\Model\Source\FilterPlacedBlock;
use Magento\Framework\View\Element\Template;

/**
 * Class FilterCollapsing
 * @package Amasty\Shopby\Block\Navigation
 */
class FilterCollapsing extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $catalogLayer;

    /**
     * @var \Amasty\Shopby\Model\Layer\FilterList
     */
    private $filterList;

    /**
     * @var \Amasty\Shopby\Helper\FilterSetting
     */
    private $filterSettingHelper;

    /**
     * @var \Amasty\Shopby\Model\Request
     */
    private $shopbyRequest;

    public function __construct(
        Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Amasty\Shopby\Model\Layer\FilterList $filterList,
        \Amasty\Shopby\Helper\FilterSetting $filterSettingHelper,
        \Amasty\Shopby\Model\Request $shopbyRequest,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->catalogLayer = $layerResolver->get();
        $this->filterList = $filterList;
        $this->filterSettingHelper = $filterSettingHelper;
        $this->shopbyRequest = $shopbyRequest;
    }

    /**
     * @return int[]
     */
    public function getFiltersExpanded()
    {
        $listExpandedFilters = [];
        $position = 0;
        foreach ($this->getFilters() as $filter) {
            if (!$filter->getItemsCount()) {
                continue;
            }

            $filterSetting = $this->filterSettingHelper->getSettingByLayerFilter($filter);
            $isApplyFilter = $this->shopbyRequest->getParam($filter->getRequestVar());
            if ($filterSetting->isExpanded() || $isApplyFilter) {
                $listExpandedFilters[] = $position;
            }

            if ($filterSetting->getBlockPosition() != FilterPlacedBlock::POSITION_TOP) {
                $position++;
            }
        }

        return $listExpandedFilters;
    }

    /**
     * @return array|\Magento\Catalog\Model\Layer\Filter\AbstractFilter[]
     */
    protected function getFilters()
    {
        return $this->filterList->getFilters($this->catalogLayer);
    }
}
