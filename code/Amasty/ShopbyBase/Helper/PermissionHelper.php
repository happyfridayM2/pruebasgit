<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Helper;

use Amasty\Shopby\Model\Layer\Filter\Category;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\ScopeInterface;

/**
 * Class PermissionHelper
 */
class PermissionHelper extends AbstractHelper
{
    const CUSTOMER_GROUPS = 'catalog/magento_catalogpermissions/grant_catalog_category_view_groups';

    const FOR_SPECIFIED_CUSTOMER_GROUP = 2;

    const PERMISSIONS_ENABLED = 'catalog/magento_catalogpermissions/enabled';

    const CATALOG_PERMISSIONS = 'catalog/magento_catalogpermissions/grant_catalog_category_view';

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Amasty\ShopbyBase\Model\Di\Wrapper
     */
    private $permissionModel;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Amasty\ShopbyBase\Model\Di\Wrapper $permissionModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->permissionModel = $permissionModel;
        $this->moduleManager = $context->getModuleManager();
        $this->storeManager = $storeManager;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkPermissions()
    {
        $isAllowed = true;
        if ($this->moduleManager->isEnabled('Magento_CatalogPermissions')
            && $this->scopeConfig->isSetFlag(self::PERMISSIONS_ENABLED, ScopeInterface::SCOPE_STORE)
        ) {
            $permissions = $this->getPermissions();
            $permissions = $permissions ? array_shift($permissions) : false;

            $isAllowed = $permissions
                && isset($permissions['grant_catalog_category_view'])
                && $permissions['grant_catalog_category_view'] !== Category::DENY_PERMISSION;
            $isAllowed = $isAllowed || ($permissions ? false : $this->isAllowedPermissions());
        }

        return $isAllowed;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        $store = $this->storeManager->getStore();
        return $this->permissionModel->getIndexForCategory(
            $store->getRootCategoryId(),
            $this->customerSession->getCustomerGroupId(),
            $store->getWebsiteId()
        );
    }

    /**
     * @return bool|mixed
     */
    private function isAllowedPermissions()
    {
        $allowCategories = $this->getCatalogCategoryPermissions();
        if ($allowCategories == self::FOR_SPECIFIED_CUSTOMER_GROUP) {
            $customerGroupId = $this->getCustomerGroupId();
            $allowedCustomerGroups = $this->getCustomerGroupPermissions();
            $allowCategories = in_array($customerGroupId, $allowedCustomerGroups);
        }

        return $allowCategories;
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->customerSession->isLoggedIn()
            ? $this->customerSession->getCustomer()->getGroupId()
            : 0;
    }

    /**
     * @return mixed
     */
    public function getCatalogCategoryPermissions()
    {
        return $this->scopeConfig->getValue(self::CATALOG_PERMISSIONS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getCustomerGroupPermissions()
    {
        $groups = $this->scopeConfig->getValue(self::CUSTOMER_GROUPS, ScopeInterface::SCOPE_STORE);
        $groups = explode(',', $groups);

        return $groups;
    }
}
