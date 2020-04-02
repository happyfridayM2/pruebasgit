<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Helper;

use Amasty\ShopbyBase\Plugin\View\Page\Title;
use Magento\Framework\Registry;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Category;

/**
 * Class Meta
 */
class Meta extends AbstractHelper
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Framework\View\Page\Config $pageConfig
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->pageConfig = $pageConfig;
    }

    /**
     * @param Category $category
     *
     * @return mixed|string
     */
    public function getOriginPageMetaTitle(Category $category)
    {
        return $category->getData('meta_title')
            ?: (string)$this->registry->registry(Title::PAGE_META_TITLE_MAIN_PART);
    }

    /**
     * @param Category $category
     *
     * @return mixed|string
     */
    public function getOriginPageMetaDescription(Category $category)
    {
        return $category->getData('meta_description') ?: $this->pageConfig->getDescription();
    }
}
