<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ProductAlert\Model\Email;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Bundle\Model\Product\Type as BundleType;
use Amasty\Xnotif\Model\Analytics\Collector;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\App\Emulation;

class Observer extends \Magento\ProductAlert\Model\Observer
{
    /**
     * @var array
     */
    private $alertFactories = [];

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableType;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var AdminNotification
     */
    private $adminNotification;

    /**
     * @var Collector
     */
    private $collector;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var array
     */
    private $productSendCache = [];

    /**
     * @var \Amasty\Xnotif\Helper\Config
     */
    private $config;

    public function __construct(
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory $priceColFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory $stockColFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\ProductAlert\Model\EmailFactory $emailFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Registry $registry,
        \Amasty\Xnotif\Helper\Data $helper,
        \Amasty\Xnotif\Helper\Config $config,
        Configurable $configurableType,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Psr\Log\LoggerInterface $logger,
        AdminNotification $adminNotification,
        Emulation $appEmulation,
        Collector $collector
    ) {
        parent::__construct(
            $catalogData,
            $scopeConfig,
            $storeManager,
            $priceColFactory,
            $customerRepository,
            $productRepository,
            $dateFactory,
            $stockColFactory,
            $transportBuilder,
            $emailFactory,
            $inlineTranslation
        );
        $this->alertFactories = [
            'price' => $priceColFactory,
            'stock' => $stockColFactory
        ];
        $this->customerFactory = $customerFactory;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->configurableType = $configurableType;
        $this->logger = $logger;
        $this->adminNotification = $adminNotification;
        $this->collector = $collector;
        $this->appEmulation = $appEmulation;
        $this->config = $config;
    }

    /**
     * @return $this
     */
    public function runDailyCronJob()
    {
        $this->sendStockEmailsWithLimit();
        $this->adminNotification->sendAdminNotifications();

        return $this;
    }

    /**
     * @param $type
     * @param Email $email
     *
     * @return $this
     */
    protected function sendNotifications($type, Email $email)
    {
        $prevCustomerEmail = null;
        $prevStoreId = null;
        $email->setType($type);
        $productNotifications = 0;
        $tempNotifications = 0;

        $collection = $this->generateCollectionByType($type);
        foreach ($collection as $alert) {
            try {
                $website = $this->_storeManager->getWebsite($alert->getWebsiteId());
                $storeId = $alert->getStoreId() ?: $website->getDefaultStore()->getId();
                $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
                $this->registerAmastyStore($storeId);
                $email->setWebsite($website)->setStoreId($storeId);

                if (!$this->isAlertEnabled($type, $website)) {
                    $this->appEmulation->stopEnvironmentEmulation();
                    continue;
                }

                $customer = $this->getCustomerFromAlert($alert, $website->getId());
                if (!$customer) {
                    $this->appEmulation->stopEnvironmentEmulation();
                    continue;
                }

                if ($customer->getEmail() !== $prevCustomerEmail ||
                    ($customer->getEmail() === $prevCustomerEmail && $prevStoreId != $alert->getStoreId())
                ) {
                    if ($prevCustomerEmail) {
                        $this->appEmulation->stopEnvironmentEmulation();
                        $this->appEmulation->startEnvironmentEmulation($prevStoreId, Area::AREA_FRONTEND, true);
                        $email->setStoreId($prevStoreId);

                        $email->send();
                        $productNotifications += $tempNotifications;
                        $tempNotifications = 0;
                        $this->deleteTemporaryEmail();
                    }

                    $email->clean();
                    $email->setCustomerData($customer);
                }

                $prevCustomerEmail = $customer->getEmail();
                $prevStoreId = $alert->getStoreId();
                if (!$customer->getId()) {
                    $this->saveTemporaryEmail($prevCustomerEmail);
                }

                $product = $this->loadProduct($alert->getProductId(), $storeId);

                if (!$product) {
                    $this->appEmulation->stopEnvironmentEmulation();
                    continue;
                }

                if ('stock' == $type) {
                    if ($this->shouldSkipByLimit($product, $website)) {
                        $this->appEmulation->stopEnvironmentEmulation();
                        continue;
                    }

                    if ($product = $this->checkStockSubscription($product, $alert, $website)) {
                        $product->setCustomerGroupId($customer->getGroupId());
                        $email->addStockProduct($product);
                        $tempNotifications++;
                    }
                } else {
                    if ($product = $this->checkPriceSubscription($product, $alert)) {
                        $email->addPriceProduct($product);
                    }
                }
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                $tempNotifications = 0;
                continue;
            }
            $this->appEmulation->stopEnvironmentEmulation();
        }

        if ($prevCustomerEmail) {
            try {
                $email->setStoreId($prevStoreId);
                $email->send();
                $productNotifications += $tempNotifications;
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
            }
        }

        if ($productNotifications) {
            $this->collector->updateDaily(Collector::ACTION_SENT, $productNotifications);
        }

        return $this;
    }

    /**
     * @param ProductInterface $product
     * @param WebsiteInterface $website
     *
     * @return bool
     */
    protected function shouldSkipByLimit(ProductInterface $product, WebsiteInterface $website)
    {
        $result = false;
        if ($this->config->isQtyLimitEnabled()) {
            $productId = $product->getId();
            if (isset($this->productSendCache[$productId])) {
                if ($this->productSendCache[$productId]['qty'] <= $this->productSendCache[$productId]['counter']) {
                    //limit- should skip next
                    $result = true;
                } else {
                    $this->productSendCache[$productId]['counter']++;
                }
            } else {
                $this->productSendCache[$productId] = [
                    'qty' => $this->helper->getProductQty($product, $website->getId()),
                    'counter' => 1
                ];
            }
        }

        return $result;
    }

    /**
     * @param int $productId
     * @param int $storeId
     *
     * @return bool|ProductInterface
     */
    protected function loadProduct($productId, $storeId)
    {
        try {
            $product = $this->productRepository->getById(
                $productId,
                false,
                $storeId
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $ex) {
            $product = false;
        }

        return $product;
    }

    /**
     * @param string $type
     * @param WebsiteInterface $website
     *
     * @return bool
     */
    protected function isAlertEnabled($type, WebsiteInterface $website)
    {
        return (bool)$this->_scopeConfig->getValue(
            'catalog/productalert/allow_' . $type,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $website->getDefaultGroup()->getDefaultStore()->getId()
        );
    }

    /**
     * @param string $type
     *
     * @return \Magento\ProductAlert\Model\ResourceModel\Stock\Collection|\Magento\ProductAlert\Model\ResourceModel\Price\Collection
     */
    protected function generateCollectionByType($type)
    {
        return $this->alertFactories[$type]->create()
            ->addFieldToFilter('status', 0)
            ->setCustomerOrder();
    }

    /**
     * @param $storeId
     */
    protected function registerAmastyStore($storeId)
    {
        if ($this->registry->registry('amasty_store_id')) {
            $this->registry->unregister('amasty_store_id');
        }
        $this->registry->register('amasty_store_id', $storeId);
    }

    /**
     * @param $product
     * @param \Magento\ProductAlert\Model\Price $alert
     * @return null
     */
    private function checkPriceSubscription($product, $alert)
    {
        if ($alert->getPrice() > $product->getFinalPrice()) {
            $productPrice = $product->getFinalPrice();
            $product->setFinalPrice(
                $this->_catalogData->getTaxPrice(
                    $product,
                    $productPrice
                )
            );
            $product->setPrice(
                $this->_catalogData->getTaxPrice(
                    $product,
                    $product->getPrice()
                )
            );

            $alert->setPrice($productPrice);
            $alert->setLastSendDate(
                $this->_dateFactory->create()->gmtDate()
            );
            $alert->delete();

            return $product;
        }

        return null;
    }

    /**
     * @param ProductInterface $product
     * @param \Magento\ProductAlert\Model\Stock $alert
     * @param WebsiteInterface $website
     * @return ProductInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function checkStockSubscription(ProductInterface $product, $alert, WebsiteInterface $website)
    {
        if ($this->getIsInStockProduct($product, $website)) {
            if ($alert->getParentId()
                && $alert->getParentId() != $alert->getProductId()
                && !$product->canConfigure()
            ) {
                $product = $this->loadProduct($alert->getParentId(), $website->getDefaultStore()->getId());
            }

            $alert->setSendDate($this->_dateFactory->create()->gmtDate());
            $alert->delete();

            return $product;
        }

        return null;
    }

    /**
     * @param ProductInterface $product
     * @param WebsiteInterface $website
     *
     * @return bool
     */
    protected function getIsInStockProduct(ProductInterface $product, WebsiteInterface $website)
    {
        $minQuantity = $this->config->getMinQty();

        $isInStock = false;
        $allProducts = $this->getUsedProducts($product);
        if ($allProducts && $product->isSalable()) {
            foreach ($allProducts as $simpleProduct) {
                $quantity = $this->helper->getProductQty($simpleProduct, $website->getId());
                $isInStock = ($this->isSalable($simpleProduct, $website) || $simpleProduct->isSaleable())
                    && $quantity >= $minQuantity;
                if ($isInStock) {
                    break;
                }
            }
        } else {
            $quantity = $this->helper->getProductQty($product, $website->getId());
            $isInStock = $this->isSalable($product, $website) && ($quantity >= $minQuantity);
        }

        return $isInStock;
    }

    /**
     * @param ProductInterface $product
     * @param WebsiteInterface $website
     *
     * @return bool
     */
    private function isSalable(ProductInterface $product, WebsiteInterface $website)
    {
        return isset($this->productSalability) ?
            $this->productSalability->isSalable($product, $website) :
            $product->isSalable();
    }

    /**
     * @param \Magento\ProductAlert\Model\Stock $alert
     * @param $websiteId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomerFromAlert($alert, $websiteId = null)
    {
        if (!$websiteId) {
            $websiteId = $this->_storeManager->getStore()->getWebsite()->getId();
        }

        if ($alert->getCustomerId()) {
            try {
                $customer = $this->customerRepository->getById(
                    $alert->getCustomerId()
                );
            } catch (NoSuchEntityException $noSuchEntityException) {
                return null;
            }
        } else {
            try {
                $customer = $this->customerRepository->get(
                    $alert->getEmail(),
                    $websiteId
                );
            } catch (NoSuchEntityException $e) {
                $customer = $this->createCustomerModel($alert->getEmail(), $websiteId);
            }
        }

        $customer->setStoreId($alert->getStoreId());

        return $customer;
    }

    /**
     * @param string $email
     * @param int $websiteId
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function createCustomerModel($email, $websiteId)
    {
        $customer = $this->customerFactory->create()->getDataModel();
        $customer->setWebsiteId(
            $websiteId
        )->setEmail(
            $email
        )->setLastname(
            $this->config->getCustomerName()
        )->setGroupId(
            0
        )->setId(
            0
        );

        return $customer;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    protected function getTestEmail()
    {
        $emailAddress = $this->config->getTestEmail();
        if (!$emailAddress) {
            throw new LocalizedException(
                __(
                    'Please specify email address: Store -> Configuration -> '
                    . 'Amasty Out of Stock Notification -> Test Stock Notification'
                )
            );
        }

        return $emailAddress;
    }

    /**
     * @param $alert
     * @throws LocalizedException
     */
    public function sendTestNotification($alert)
    {
        $alert->setCustomerId(null)->setEmail($this->getTestEmail());

        /** @var \Magento\ProductAlert\Model\Email  $email */
        $email = $this->_emailFactory->create();
        $email->setType('stock');

        $websiteId = $alert->getWebsiteId();
        $websiteId = explode(',', $websiteId);
        $websiteId = $websiteId[0];

        $website = $this->_storeManager->getWebsite($websiteId);
        $storeId = $alert->getStoreId() ? $alert->getStoreId() : $website->getDefaultStore()->getId();
        $email->setWebsite($website)->setStoreId($storeId);
        if (!$this->_scopeConfig->getValue(
            'catalog/productalert/allow_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $website->getDefaultGroup()->getDefaultStore()->getId()
        )) {
            throw new LocalizedException(
                __('Please enable stock notifications: Store -> Configuration -> Catalog -> Alert')
            );
        }

        $productId = $alert->getProductId();
        if ($alert->getParentId()
            && $alert->getParentId() != $productId
        ) {
            $productId = $alert->getParentId();
        }

        $this->appEmulation->startEnvironmentEmulation($storeId, 'frontend', true);
        $product = $this->loadProduct($productId, $storeId);
        $customer = $this->getCustomerFromAlert($alert, $websiteId);
        $product->setCustomerGroupId($customer->getGroupId());

        $email->addStockProduct($product);
        $email->setCustomerData($customer);
        if (!$customer->getId()) {
            $this->saveTemporaryEmail($customer->getEmail());
        }

        $this->registry->register('xnotif_test_notification', true);
        $email->send();
        $this->registry->unregister('xnotif_test_notification');
        $this->appEmulation->stopEnvironmentEmulation();
    }

    /**
     * Save guest email for current iteration
     * @param string $email
     */
    private function saveTemporaryEmail($email)
    {
        $this->deleteTemporaryEmail();
        $this->registry->register(
            'amxnotif_data',
            [
                'guest' => 1,
                'email' => $email
            ]
        );
    }

    private function deleteTemporaryEmail()
    {
        $this->registry->unregister('amxnotif_data');
    }

    /**
     * @param ProductInterface $product
     *
     * @return array|ProductInterface[]
     */
    private function getUsedProducts(ProductInterface $product)
    {
        $result = [];
        switch ($product->getTypeId()) {
            case Configurable::TYPE_CODE:
                $result = $this->configurableType->getUsedProducts($product);
                break;
            case Grouped::TYPE_CODE:
                $result = $product->getTypeInstance(true)->getAssociatedProducts($product);
                break;
            case BundleType::TYPE_CODE:
                $result = $product->getTypeInstance(true)->getSelectionsCollection(
                    $product->getTypeInstance(true)->getOptionsIds($product),
                    $product
                );
                break;
        }

        return $result;
    }

    /**
     * @return void
     */
    protected function sendStockEmailsWithLimit()
    {
        /** @var \Magento\ProductAlert\Model\Email $email */
        $email = $this->_emailFactory->create();
        $this->sendNotifications('stock', $email);
        $this->_sendErrorEmail();
    }

    /**
     * @inheritdoc
     */
    protected function _processStock(Email $email)
    {
        if (!$this->config->isQtyLimitEnabled()) {
            $this->sendNotifications('stock', $email);
        }
    }

    /**
     * @inheritdoc
     */
    protected function _processPrice(Email $email)
    {
        $this->sendNotifications('price', $email);
    }

    /**
     * @return \Magento\ProductAlert\Model\Observer|void
     */
    protected function _sendErrorEmail()
    {
        if (!empty($this->_errors)) {
            foreach ($this->_errors as $error) {
                $this->logger->error($error);
            }
            parent::_sendErrorEmail();
        }
    }
}
