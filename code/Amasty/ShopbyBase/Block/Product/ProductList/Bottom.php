<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Block\Product\ProductList;

use Magento\Framework\View\Element\Template;

class Bottom extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    /**
     * @return string
     */
    public function getCmsBlockHtml()
    {
        $currentCategory = $this->registry->registry('current_category');
        $blockId = $currentCategory ? $currentCategory->getBottomCmsBlock() : 0;
        if ($blockId) {
            $html = $this->getLayout()
            ->createBlock(\Magento\Cms\Block\Block::class)
            ->setBlockId($blockId)
            ->toHtml();
        }

        return $html ?? '';
    }
}
