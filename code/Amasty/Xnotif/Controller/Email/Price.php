<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Controller\Email;

use Amasty\Xnotif\Model\Source\Group;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Price
 */
class Price extends AbstractEmail
{
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
     * @var \Magento\ProductAlert\Model\PriceFactory
     */
    private $priceFactory;

    /**
     * @var \Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory
     */
    private $priceCollectionFactory;

    /**
     * @var \Amasty\Xnotif\Helper\Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ProductAlert\Model\PriceFactory $priceFactory,
        \Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory $priceCollectionFactory,
        \Amasty\Xnotif\Helper\Config $config
    ) {
        parent::__construct($context, $customerSessionFactory, $storeManager, $productRepository, $config);
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->redirectFactory = $context->getResultRedirectFactory();
        $this->priceFactory = $priceFactory;
        $this->priceCollectionFactory = $priceCollectionFactory;
        $this->config = $config;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $backUrl = $this->getRequest()->getParam(Action::PARAM_NAME_URL_ENCODED);
        $guestEmail = $this->getRequest()->getParam('guest_email_price');
        $parentId = (int)$this->getRequest()->getParam('parent_id', null);
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerId = $this->getCustomerSession()->getId();

        $redirect = $this->redirectFactory->create();
        if (!$backUrl) {
            $redirect->setUrl('/');
            return $redirect;
        }

        try {
            $this->validateGDRP($data);

            $product = $this->initProduct();
            $productId = $product->getId();
            if ($productId == $parentId) {
                $parentId = null;
            }

            /** @var \Magento\ProductAlert\Model\Price $model */
            $model = $this->priceFactory->create()
                ->setCustomerId($customerId)
                ->setProductId($productId)
                ->setPrice($product->getFinalPrice())
                ->setWebsiteId($websiteId)
                ->setParentId($parentId);

            $collection = $this->getAlertCollection($websiteId, $productId);
            if ($guestEmail) {
                $guestEmail = $this->validateEmail($guestEmail);

                try {
                    $customer = $this->customerRepository->get($guestEmail, $websiteId);
                    $model->setCustomerId($customer->getId());
                    $collection->addFieldToFilter('customer_id', $customer->getId());
                } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                    //this is guest
                    $model->setEmail($guestEmail);
                    $collection->addFieldToFilter('email', $guestEmail);
                }
            } else {
                $model->setCustomerId($customerId);
                $collection->addFieldToFilter('customer_id', $customerId);
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

        $redirect->setUrl($this->_redirect->getRefererUrl());
        return $redirect;
    }

    /**
     * @param int $websiteId
     * @param int $productId
     *
     * @return \Magento\ProductAlert\Model\ResourceModel\Price\Collection
     */
    protected function getAlertCollection($websiteId, $productId)
    {
        return $this->priceCollectionFactory->create()
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
        return $this->config->getAllowedPriceCustomerGroups();
    }
}
