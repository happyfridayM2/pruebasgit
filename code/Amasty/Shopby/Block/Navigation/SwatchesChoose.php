<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Navigation;

use Magento\Framework\View\Element\Template;

/**
 * Class SwatchesChoose
 * @package Amasty\Shopby\Block\Navigation
 */
class SwatchesChoose extends Template
{
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $catalogLayer;
    /**
     * @var \Magento\Catalog\Model\Layer\FilterList
     */
    protected $filterList;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchHelper;
    /**
     * @var \Amasty\Shopby\Helper\FilterSetting
     */
    protected $filterSettingHelper;

    /**
     * @var \Amasty\Shopby\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * SwatchesChoose constructor.
     *
     * @param Template\Context                        $context
     * @param \Magento\Catalog\Model\Layer\Resolver   $layerResolver
     * @param \Magento\Catalog\Model\Layer\FilterList $filterList
     * @param \Magento\Swatches\Helper\Data           $swatchHelper
     * @param \Amasty\Shopby\Helper\FilterSetting     $filterSettingHelper
     * @param array                                   $data
     */
    public function __construct(
        Template\Context $context,
        \Amasty\Shopby\Model\Request $shopbyRequest,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Amasty\Shopby\Model\Layer\FilterList $filterList,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Amasty\Shopby\Helper\FilterSetting $filterSettingHelper,
        \Amasty\Shopby\Api\GroupRepositoryInterface $groupRepository,
        array $data = []
    ) {
        $this->catalogLayer = $layerResolver->get();
        $this->filterList = $filterList;
        $this->request = $shopbyRequest;
        $this->swatchHelper = $swatchHelper;
        $this->filterSettingHelper = $filterSettingHelper;
        $this->groupRepository = $groupRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSwatchesByJson()
    {
        $result = [];
        foreach ($this->filterList->getAllFilters($this->catalogLayer) as $filter) {
            if (!$filter->getItemsCount()) {
                continue;
            }
            if ($filter->hasAttributeModel()) {
                $appliedValue = $this->request->getParam($filter->getRequestVar(), false);
                $appliedValue = $this->escapeHtml($appliedValue);
                if (!$appliedValue) {
                    continue;
                }
                $appliedValue = explode(",", $appliedValue);

                $groupExist = false;

                foreach ($appliedValue as $key => $value) {
                    $group = $this->groupRepository->getGroupOptionsIds($value);

                    if ($group) {
                        $groupExist = true;
                    } else {
                        continue;
                    }

                    unset($appliedValue[array_search($value, $appliedValue)]);
                    // @codingStandardsIgnoreLine
                    $appliedValue = array_merge($appliedValue, $group);
                    $appliedValue = array_unique($appliedValue);
                }

                $filterSetting = $this->filterSettingHelper->getSettingByLayerFilter($filter);
                $clickInsteadMagento = $filterSetting->isSeoSignificant() || count($appliedValue) > 1 || $groupExist;

                if ($clickInsteadMagento) {
                    // @codingStandardsIgnoreLine
                    $result = array_merge($appliedValue, $result);
                }
            }
        }

        return json_encode($result);
    }
}
