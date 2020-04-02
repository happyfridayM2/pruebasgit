<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Model;

use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class Page
 *
 * @package Amasty\ShopbyPage\Model
 */
class Page extends AbstractExtensibleModel
{
    /**
     * Position of placing meta data in category
     */
    const POSITION_REPLACE = 'replace';
    const POSITION_AFTER = 'after';
    const POSITION_BEFORE = 'before';

    const CATEGORY_FORCE_USE_CANONICAL = 'amshopby_page_force_use_canonical';
    const MATCHED_PAGE = 'amshopby_matched_page';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\ShopbyPage\Model\ResourceModel\Page::class);
    }
}
