<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Category\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Category Collection
 *
 * @package Amasty\Feed
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\Feed\Model\Category\Category::class,
            \Amasty\Feed\Model\Category\ResourceModel\Category::class
        );
    }

    /**
     * Add google setup filter
     *
     * @return $this
     */
    public function addGoogleSetupFilter()
    {
        $this->addFieldToFilter(
            'code',
            ['like' => "google_category_%"]
        );

        return $this;
    }
}
