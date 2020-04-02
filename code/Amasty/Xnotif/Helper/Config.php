<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Helper;

use Amasty\Xnotif\Model\Source\Group;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 */
class Config extends AbstractHelper
{
    const MODULE_PATH = 'amxnotif/';

    const XML_PATH_LOW_STOCK_CONFIG = 'admin_notifications/low_stock_alert';

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\SessionFactory $sessionFactory
    ) {
        parent::__construct($context);
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @param $path
     * @param int $storeId
     * @return mixed
     */
    public function getModuleConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::MODULE_PATH . $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return bool
     */
    public function isLowStockNotifications()
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_LOW_STOCK_CONFIG);
    }

    /**
     * Check if popup on
     *
     * @return int
     */
    public function isPopupForSubscribeEnabled()
    {
        return (int)$this->getModuleConfig('stock/with_popup');
    }

    /**
     * @return bool
     */
    public function isQtyLimitEnabled()
    {
        return (bool)$this->getModuleConfig('stock/email_limit');
    }

    /**
     * @return bool
     */
    public function isCategorySubscribeEnabled()
    {
        return (bool)$this->getModuleConfig('stock/subscribe_category');
    }

    /**
     * @return bool
     */
    public function isGDRPEnabled()
    {
        return (bool)$this->getModuleConfig('gdrp/enabled');
    }

    /**
     * @return string
     */
    public function getGDRPText()
    {
        return $this->getModuleConfig('gdrp/text');
    }

    /**
     * @return bool
     */
    public function isAdminNotificationEnabled()
    {
        return (bool)$this->getModuleConfig('admin_notifications/notify_admin');
    }

    /**
     * @return bool
     */
    public function isParentImageEnabled()
    {
        return (bool)$this->getModuleConfig('general/account_image');
    }

    /**
     * @return string
     */
    public function getPlaceholder()
    {
        return (string)$this->getModuleConfig('stock/placeholder');
    }

    /**
     * @return string
     */
    public function getAdminNotificationEmail()
    {
        return (string)$this->getModuleConfig('admin_notifications/stock_alert_email');
    }

    /**
     * @return string
     */
    public function getAdminNotificationSender()
    {
        return (string)$this->getModuleConfig('admin_notifications/sender_email_identity');
    }

    /**
     * @return string
     */
    public function getAdminNotificationTemplate()
    {
        return (string)$this->getModuleConfig('admin_notifications/notify_admin_template');
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        return (string)$this->getModuleConfig('general/customer_name');
    }

    /**
     * @return string
     */
    public function getTestEmail()
    {
        $email = (string)$this->getModuleConfig('general/test_notification_email');
        $email = $this->validateEmail($email);

        return $email;
    }

    /**
     * @return int
     */
    public function getMinQty()
    {
        $minQuantity = (int)$this->getModuleConfig('general/min_qty');
        $minQuantity = ($minQuantity < 1) ? 1 : $minQuantity;

        return $minQuantity;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        $customerSession = $this->sessionFactory->create();

        return $customerSession->getCustomerId()
            && $customerSession->checkCustomerId($customerSession->getCustomerId());
    }

    /**
     * @return array
     */
    public function getAllowedPriceCustomerGroups()
    {
        $allowedGroups = $this->getModuleConfig('price/customer_group');

        return explode(',', $allowedGroups);
    }

    /**
     * @return array
     */
    public function getAllowedStockCustomerGroups()
    {
        $allowedGroups = $this->getModuleConfig('stock/customer_group');

        return explode(',', $allowedGroups);
    }

    /**
     * @param $type
     * @return bool
     */
    public function allowForCurrentCustomerGroup($type)
    {
        if ($type == 'stock') {
            $allowedGroups = $this->getAllowedStockCustomerGroups();
        } else {
            $allowedGroups = $this->getAllowedPriceCustomerGroups();
        }

        if (in_array(Group::ALL_GROUPS, $allowedGroups)) {
            return true;
        }

        return in_array($this->getCustomerGroupId(), $allowedGroups);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getQuantityBelow($storeId)
    {
        return $this->getModuleConfig('admin_notifications/qty_below', $storeId);
    }

    /**
     * @return int
     */
    protected function getCustomerGroupId()
    {
        return $this->sessionFactory->create()->getCustomerGroupId();
    }

    /**
     * @param $email
     *
     * @return string
     */
    protected function validateEmail($email)
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!\Zend_Validate::is($email, 'EmailAddress')) {
            $email = '';
        }

        return $email;
    }
}
