<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

class ListProductAbstract
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Amasty\Conf\Helper\Data
     */
    private $configHelper;

    /**
     * ListProduct constructor.
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Amasty\Conf\Helper\Data $configHelper
     */
    public function __construct(
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Amasty\Conf\Helper\Data $configHelper
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->configHelper = $configHelper;
    }

    /**
     * @param ListProduct $listBlock
     * @return string
     */
    public function generateFlipperConfig(
        ListProduct $listBlock
    ) {
        $result = '';

        /** @var AbstractCollection $collection */
        $collection = $listBlock->getLoadedProductCollection();
        if ($collection->getSize() && $this->configHelper->isFlipperEnabled()) {
            $layout = $this->layoutFactory->create();
            /** @var \Amasty\Conf\Block\Flipper $flipperBlock */
            $flipperBlock = $layout->createBlock(
                \Amasty\Conf\Block\Flipper::class,
                'amasty.conf.flipper',
                ['data' => []]
            );

            $result = $flipperBlock->applyFlipperToCollection($collection, $listBlock);
        }

        return $result;
    }
}
