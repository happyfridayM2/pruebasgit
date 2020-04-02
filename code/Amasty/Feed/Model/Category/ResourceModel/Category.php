<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Category\ResourceModel;

use Amasty\Feed\Model\Category\Category as ModelCategory;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

/**
 * Class Category Resource Model
 *
 * @package Amasty\Feed
 */
class Category extends AbstractDb
{
    const TABLE_NAME = 'amasty_feed_category';

    /**
     * Initialize table nad PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'feed_category_id');
    }
}
