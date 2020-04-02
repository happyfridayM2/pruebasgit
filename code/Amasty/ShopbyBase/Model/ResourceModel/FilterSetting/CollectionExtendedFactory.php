<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model\ResourceModel\FilterSetting;

/**
 * FilterSetting CollectionExtendedFactory
 */
class CollectionExtendedFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Provide Filter Setting Collection Extended instance
     *
     * @param array $arguments
     *
     * @return CollectionExtended
     * @throws \UnexpectedValueException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(array $arguments = [])
    {
        return $this->get();
    }

    /**
     * @return CollectionExtended
     * @throws \UnexpectedValueException
     */
    public function get()
    {
        return $this->objectManager->get(CollectionExtended::class);
    }
}
