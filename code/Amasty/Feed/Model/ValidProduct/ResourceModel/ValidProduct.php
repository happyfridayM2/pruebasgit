<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\ValidProduct\ResourceModel;

use Amasty\Feed\Api\Data\ValidProductsInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

/**
 * Class ValidProduct
 *
 * @package Amasty\Feed
 */
class ValidProduct extends AbstractDb
{
    const TABLE_NAME = 'amasty_feed_valid_products';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ValidProductsInterface::ENTITY_ID);
    }
}
