<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\FieldMapper;

use Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\AdditionalFieldMapperInterface;

/**
 * Class OnSale
 * @package Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\FieldMapper
 */
class OnSale implements AdditionalFieldMapperInterface
{
    const ATTRIBUTE_NAME = 'am_on_sale';
    const ATTRIBUTE_TYPE = 'integer';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    private $customerGroupCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * OnSale constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
    }

    /**
     * @return array
     */
    public function getAdditionalAttributeTypes()
    {
        $groupCollection = $this->customerGroupCollectionFactory->create();
        $websites = $this->storeManager->getWebsites();
        $attributeTypes = [];
        foreach ($groupCollection as $group) {
            foreach ($websites as $website) {
                $attributeTypes[self::ATTRIBUTE_NAME . '_' . $group->getId() . '_' . $website->getId()] =
                    ['type' => self::ATTRIBUTE_TYPE];
            }
        }
        return $attributeTypes;
    }

    /**
     * @param array $context
     * @return string
     */
    public function getFiledName($context)
    {
        $customerGroupId = !empty($context['customerGroupId'])
            ? $context['customerGroupId']
            : $this->customerSession->getCustomerGroupId();
        $websiteId = !empty($context['websiteId'])
            ? $context['websiteId']
            : $this->storeManager->getStore()->getWebsiteId();
        return self::ATTRIBUTE_NAME . '_' . $customerGroupId . '_' . $websiteId;
    }
}
