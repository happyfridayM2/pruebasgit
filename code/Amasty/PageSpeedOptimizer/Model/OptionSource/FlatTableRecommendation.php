<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\OptionSource;

use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\Option\ArrayInterface;

class FlatTableRecommendation implements ArrayInterface
{
    const NO = 0;
    const YES = 1;

    /**
     * @var \Amasty\Base\Model\MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    public function __construct(
        \Amasty\Base\Model\MagentoVersion $magentoVersion,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    ) {
        $this->magentoVersion = $magentoVersion;
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $widgetType => $label) {
            $optionArray[] = ['value' => $widgetType, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        if (version_compare($this->magentoVersion->get(), '2.3.0', '>=')
            && in_array(
                $this->config->getValue(Custom::XML_PATH_CATALOG_SEARCH_ENGINE),
                ['elasticsearch5', 'elasticsearch6', 'elasticsearch']
            )
        ) {
            return [
                self::YES => __('Yes'),
                self::NO => __('No (Recommended)'),
            ];
        }

        return [
            self::YES => __('Yes (Recommended)'),
            self::NO => __('No'),
        ];
    }
}
