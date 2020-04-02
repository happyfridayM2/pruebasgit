<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Field;

/**
 * Class Field
 *
 * @package Amasty\Feed
 */
class Field extends \Magento\Framework\Model\AbstractModel
{
    const FEED_FIELD_ID = 'feed_field_id';

    protected function _construct()
    {
        $this->_init(ResourceModel\Field::class);
        $this->setIdFieldName(self::FEED_FIELD_ID);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getData('code');
    }
}
