<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Layer\Filter;

use Amasty\Shopby;
use \Amasty\ShopbyBase\Model\FilterSetting;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var  Shopby\Helper\FilterSetting
     */
    protected $filterSettingHelper;

    /**
     * @var  Shopby\Helper\UrlBuilder
     */
    protected $urlBuilderHelper;

    /**
     * @var \Amasty\Shopby\Helper\Group
     */
    private $groupHelper;

    /**
     * @var FilterSetting
     */
    private $filterSetting;

    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        \Magento\Framework\App\Request\Http $request,
        Shopby\Helper\FilterSetting $filterSettingHelper,
        Shopby\Helper\UrlBuilder $urlBuilderHelper,
        \Amasty\Shopby\Helper\Group $groupHelper,
        FilterSetting $filterSetting,
        array $data = []
    ) {
        $this->_request = $request;
        $this->filterSettingHelper = $filterSettingHelper;
        $this->urlBuilderHelper = $urlBuilderHelper;
        $this->groupHelper = $groupHelper;
        parent::__construct($url, $htmlPagerBlock, $data);
        $this->filterSetting = $filterSetting;
    }
    /**
     * Get filter item url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->urlBuilderHelper->buildUrl($this->getFilter(), $this->getValue());
    }

    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl($value = null)
    {
        $value = $value !== null ? $value : $this->getValue();

        return $this->urlBuilderHelper->buildUrl($this->getFilter(), $value);
    }

    /**
     * @return bool
     */
    public function isAddNofollow()
    {
        return $this->filterSetting->isAddNofollow();
    }

    /**
     * @return string
     */
    public function getOptionLabel()
    {
        return $this->groupHelper->chooseGroupLabel($this->getLabel());
    }
}
