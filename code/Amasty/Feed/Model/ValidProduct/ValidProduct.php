<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\ValidProduct;

use Amasty\Feed\Api\Data\ValidProductsInterface;

/**
 * Class ValidProduct
 *
 * @package Amasty\Feed
 */
class ValidProduct extends \Magento\Framework\Model\AbstractModel implements ValidProductsInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Amasty\Feed\Model\ValidProduct\ResourceModel\ValidProduct::class);
        $this->setIdFieldName(ValidProductsInterface::ENTITY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getFeedId()
    {
        return $this->_getData(ValidProductsInterface::FEED_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setFeedId($feedId)
    {
        return $this->setData(ValidProductsInterface::FEED_ID, $feedId);
    }

    /**
     * {@inheritdoc}
     */
    public function getValidProductId()
    {
        return $this->_getData(ValidProductsInterface::VALID_PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setValidProductId($validProducts)
    {
        return $this->setData(ValidProductsInterface::VALID_PRODUCT_ID, $validProducts);
    }
}
