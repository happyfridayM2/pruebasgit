<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ProductAlert\Model\StockFactory;
use Magento\ProductAlert\Model\Stock;
use Magento\ProductAlert\Model\ResourceModel\Stock as StockResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Save
 */
class Save extends Action
{
    /**
     * @var StockFactory
     */
    private $stockFactory;

    /**
     * @var StockResource
     */
    private $stockResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        StockFactory $stockFactory,
        StockResource $stockResource,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->stockFactory = $stockFactory;
        $this->stockResource = $stockResource;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $subscriptionId = $this->getRequest()->getParam('alert_stock_id');
        if ($postValue = $this->getRequest()->getPostValue()) {
            try {
                $stockSubscription = $this->stockFactory->create();
                if ($subscriptionId) {
                    $this->stockResource->load($stockSubscription, $subscriptionId);
                }

                $updateData = $this->prepareData($postValue);
                $stockSubscription->addData($updateData);
                $this->stockResource->save($stockSubscription);
                $this->messageManager->addSuccessMessage(__('You saved the item.'));

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setUrl($this->getUrl('*/*/edit', ['id' => $stockSubscription->getId()]));
                } else {
                    $resultRedirect->setUrl($this->getUrl('*/*/'));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                if ($subscriptionId) {
                    $resultRedirect->setUrl($this->getUrl('*/*/edit', ['id' => $subscriptionId]));
                } else {
                    $resultRedirect->setUrl($this->getUrl('*/*/new'));
                }
            }
        }

        return $resultRedirect;
    }

    /**
     * @param array $postValue
     *
     * @return array
     * @throws LocalizedException
     */
    private function prepareData($postValue)
    {
        $store = $this->storeManager->getStore($postValue['store_id']);
        $postValue['website_id'] = $store->getWebsiteId();

        try {
            $product = $this->productRepository->get($postValue['product_sku']);
            $postValue['product_id'] = $product->getId();
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Please enter a valid Product SKU'));
        }

        try {
            $customer = $this->customerRepository->get($postValue['email']);
            $postValue['customer_id'] = $customer->getId();

            // customer info getting from customer table
            $postValue['email'] = null;
            $postValue['store_id'] = null;
        } catch (NoSuchEntityException $e) {
            $postValue['customer_id'] = 0;
        }

        return $postValue;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Xnotif::subscription');
    }
}
