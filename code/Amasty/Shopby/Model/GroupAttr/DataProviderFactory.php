<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\GroupAttr;

/**
 * Class DataProviderFactory
 * @package Amasty\Shopby\Model\GroupAttr
 */
class DataProviderFactory implements \Amasty\ShopbyBase\Api\Group\Attribute\DataFactoryProviderInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = \Amasty\Shopby\Model\GroupAttr\DataProvider::class;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Wrapper for self::getInstance()
     *
     * @param array $data
     * @return \Amasty\Shopby\Model\GroupAttr\DataProvider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(array $data = [])
    {
        return $this->getInstance();
    }

    /**
     * Get created class instance
     *
     * @return \Amasty\Shopby\Model\GroupAttr\DataProvider
     */
    public function getInstance()
    {
        return $this->objectManager->get($this->_instanceName);
    }
}
