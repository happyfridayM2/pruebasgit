<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Email;

use Magento\Framework\Exception\LocalizedException;

class Stock extends AbstractEmail
{
    const STATUS_PENDING = 0;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var \Magento\ProductAlert\Model\StockFactory
     */
    private $stockFactory;

    /**
     * @var \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory
     */
    private $stockCollectionFactory;

    /**
     * @var \Amasty\Xnotif\Helper\Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ProductAlert\Model\StockFactory $stockFactory,
        \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory $stockCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Amasty\Xnotif\Helper\Config $config
    ) {
        parent::__construct($context, $customerSessionFactory, $storeManager, $productRepository, $config);
        $this->customerSessionFactory = $customerSessionFactory;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->redirectFactory = $context->getResultRedirectFactory();
        $this->stockFactory = $stockFactory;
        $this->stockCollectionFactory = $stockCollectionFactory;
        $this->config = $config;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $backUrl = $this->getRequest()->getParam(\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED);
        $data = $this->getRequest()->getParams();

        $redirect = $this->redirectFactory->create();
        if (!$backUrl) {
            $redirect->setUrl('/');
            return $redirect;
        }

        try {
            $this->validateGDRP($data);

            $this->updateSubscription();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Not enough parameters.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Unable to update the alert subscription.'));
        }

        $redirect->setUrl($this->_redirect->getRefererUrl());
        return $redirect;
    }

    /**
     * @throws LocalizedException
     */
    private function updateSubscription()
    {
        $productId = (int)$this->getRequest()->getParam('product_id');
        $guestEmail = $this->getRequest()->getParam('guest_email');
        $parentId = (int)$this->getRequest()->getParam('parent_id') ?: $productId;
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        try {
            $product = $this->initProduct();

            /** @var \Magento\ProductAlert\Model\Stock $model */
            $model = $this->stockFactory->create()
                ->setProductId($product->getId())
                ->setWebsiteId($websiteId)
                ->setStoreId($this->storeManager->getStore()->getId())
                ->setParentId($parentId);

            $collection = $this->getStockCollection($websiteId, $productId);
            if ($guestEmail) {
                $guestEmail = $this->validateEmail($guestEmail);

                try {
                    $customer = $this->customerRepository->get($guestEmail, $websiteId);
                    $model->setCustomerId($customer->getId());
                    $collection->addFieldToFilter('customer_id', $customer->getId());
                } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                    //this is guest
                    $model->setEmail($guestEmail);
                    if ($subscription = $this->getSubscription($model)) {
                        $model->addData($subscription->getData());
                    }
                    $collection->addFieldToFilter('email', $guestEmail);
                }
            } else {
                $model->setCustomerId($this->getCustomerSession()->getId());
                $collection->addFieldToFilter('customer_id', $this->getCustomerSession()->getId());
            }

            if ($collection->getSize() > 0) {
                $this->messageManager->addSuccessMessage(__('Thank you! You are already subscribed to this product.'));
            } else {
                $model->save();
                $this->messageManager->addSuccessMessage(__('Alert subscription has been saved.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Unable to update the alert subscription.'));
        }
    }

    /**
     * @param int $websiteId
     * @param int $productId
     *
     * @return \Magento\ProductAlert\Model\ResourceModel\Stock\Collection
     */
    protected function getStockCollection($websiteId, $productId)
    {
        return $this->stockCollectionFactory->create()
            ->addWebsiteFilter($websiteId)
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('status', 0)
            ->setCustomerOrder();
    }

    /**
     * @return array
     */
    public function getAllowedCustomerGroups()
    {
        return $this->config->getAllowedStockCustomerGroups();
    }

    /**
     * @param \Magento\ProductAlert\Model\Stock $model
     *
     * @return \Magento\Framework\DataObject
     */
    private function getSubscription($model)
    {
        $subscription = $this->stockCollectionFactory->create()
            ->addWebsiteFilter($model->getWebsiteId())
            ->addFieldToFilter('product_id', $model->getProductId())
            ->addFieldToFilter('email', $model->getEmail())
            ->setPageSize(1)
            ->getFirstItem();

        $subscription->setStatus(self::STATUS_PENDING);

        return $subscription;
    }
}
