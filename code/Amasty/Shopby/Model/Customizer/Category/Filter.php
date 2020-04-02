<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Customizer\Category;

use Amasty\ShopbyBase\Api\CategoryDataSetterInterface;
use Magento\Catalog\Model\Category;
use Amasty\ShopbyBase\Model\Customizer\Category\CustomizerInterface;

/**
 * Class Filter
 * @package Amasty\Shopby\Model\Customizer\Category
 */
class Filter implements CustomizerInterface
{
    /**
     * @var CustomizerInterface
     */
    protected $_contentHelper;

    /**
     * @param CategoryDataSetterInterface $contentHelper
     */
    public function __construct(CategoryDataSetterInterface $contentHelper)
    {
        $this->_contentHelper = $contentHelper;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function prepareData(Category $category)
    {
        $this->_contentHelper->setCategoryData($category);
        return $this;
    }
}
