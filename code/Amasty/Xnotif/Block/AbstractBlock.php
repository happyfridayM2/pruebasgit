<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use \Magento\Framework\View\Element\Template;
use \Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class AbstractBlock
 */
class AbstractBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    private $alertType;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableModel;

    /**
     * @var \Amasty\Xnotif\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\Xnotif\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Amasty\Xnotif\Model\ResourceModel\Product\Collection
     */
    private $subscriptions;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    public function __construct(
        Template\Context $context,
        Session $customerSession,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableModel,
        \Amasty\Xnotif\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Amasty\Xnotif\Helper\Config $configHelper,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
        $this->configurableModel = $configurableModel;
        $this->collectionFactory = $collectionFactory;
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
        $this->stockRegistry = $stockRegistry;
    }

    public function _construct()
    {
        $this->setTemplate('subscription.phtml');
        $this->loadCollection();
    }

    private function loadCollection()
    {
        /** @var \Amasty\Xnotif\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create()
            ->addPriceData();
        $collection->addSubscriptionData(
            $this->getAlertType(),
            $this->customerSession->getCustomerId(),
            $this->customerSession->getCustomer()->getEmail()
        );

        $this->setSubscriptions($collection);
    }

    /**
     * @param $subscription
     *
     * @return string
     */
    public function getRemoveUrl($subscription)
    {
        $type = $this->getAlertType();
        $id = $subscription->getData('alert_' . $type . '_id');

        return $this->getUrl(
            'xnotif/' . $type . '/remove',
            ['item' => $id]
        );
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getSupperAttributesByChildId($id)
    {
        $parentIds = $this->configurableModel->getParentIdsByChild($id);
        $attributes = [];
        if (!empty($parentIds)) {
            $product = $this->getProduct($parentIds[0]);
            $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
        }
        
        return $attributes;
    }

    /**
     * @param ProductInterface $product
     *
     * @return string
     */
    private function getUrlHash(ProductInterface $product)
    {
        $attributes = $this->getSupperAttributesByChildId($product->getId());
        $hash = '';
        
        if (!empty($attributes)) {
            foreach ($attributes as $attribute) {
                $attributeCode = $attribute->getData('product_attribute')->getData('attribute_code');
                $value = $product->getData($attributeCode);
                $hash .= '&' . $attributeCode . "=" . $value;
            }

            $hash = '#' . substr($hash, 1);//remove first &
        }
        
        return $hash;
    }

    /**
     * @param $product
     *
     * @return string
     */
    public function getUrlProduct(ProductInterface $product)
    {
        $parentId = $product->getParentId();
        if ($parentId) {
            $product = $this->getProduct($parentId);
        }

        return $this->generateProductUrl($product, $product->getEntityId());
    }

    /**
     * @param ProductInterface $product
     * @param $entityId
     *
     * @return string
     */
    protected function generateProductUrl(ProductInterface $product, $entityId)
    {
        $url = $product->getUrlModel()->getUrl($product);
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $hash = $this->getUrlHash($this->getProduct($entityId));
            $url = $url . $hash;
        }

        return $url;
    }

    /**
     * @param $productId
     *
     * @return ProductInterface|null
     */
    protected function getProduct($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $exception) {
            $product = null;
        }

        return $product;
    }

    /**
     * @param ProductInterface $product
     *
     * @return string
     */
    public function getImageSrc(ProductInterface $product)
    {
        if ($this->isParentImageEnabled()) {
            $parentId = $this->getParentProductId($product->getId());
            if ($parentId) {
                $product = $this->getProduct($parentId);
            }
        }

        return $this->getProductImage($product);
    }

    /**
     * @param $childId
     *
     * @return int|null
     */
    protected function getParentProductId($childId)
    {
        $result = null;
        $parentIds = $this->configurableModel->getParentIdsByChild($childId);
        if (isset($parentIds[0])) {
            $result = $parentIds[0];
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function isParentImageEnabled()
    {
        return $this->configHelper->isParentImageEnabled();
    }

    /**
     * @param ProductInterface $product
     *
     * @return string
     */
    protected function getProductImage(ProductInterface $product)
    {
        return $this->imageHelper->init($product, 'amasty_xnotif_customer_account')
            ->setImageFile($product->getImage())
            ->getUrl();
    }

    public function getConfirmationText()
    {
        //sniffer think that it is sql query
        $text = 'Are you sure you would like' . ' to remove this item from the subscriptions?';

        return __($text);
    }

    /**
     * @return string
     */
    public function getAlertType()
    {
        return $this->alertType;
    }

    /**
     * @param $alertType
     * @return $this
     */
    public function setAlertType($alertType)
    {
        $this->alertType = $alertType;
        return $this;
    }

    /**
     * @param ProductInterface $product
     *
     * @return \Magento\Framework\Phrase
     */
    public function getStockStatus(ProductInterface $product)
    {
        if ($this->isProductSalable($product)) {
            $status = __('In Stock');
        } else {
            $status = __('Out of Stock');
        }

        return $status;
    }

    /**
     * @param ProductInterface $product
     *
     * @return bool
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function isProductSalable(ProductInterface $product)
    {
        return $this->stockRegistry->getStockStatusBySku(
            $product->getSku(),
            $this->_storeManager->getWebsite()->getId()
        )->getStockStatus();
    }

    /**
     * @param $product
     *
     * @return string
     */
    public function getPriceBlock($product)
    {
        return $this->getChildBlock('price.render')
            ->setProduct($product)
            ->toHtml();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subscriptions
     */
    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }
}
