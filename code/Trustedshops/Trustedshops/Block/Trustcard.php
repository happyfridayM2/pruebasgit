<?php
/**
 * @category  Trustedshops
 * @package   Trustedshops\Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */

namespace Trustedshops\Trustedshops\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class Trustcard extends Base
{
    /**
     * magento order
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var bool
     */
    protected $orderLoaded = false;

    /**
     * remember gtin, mpn and brand attribute
     *
     * @var array
     */
    protected $attributeCache = [];

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Attribute
     */
    protected $attributeEntity;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * Trustcard constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param Session $checkoutSession
     * @param Attribute $attributeEntity
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Session $checkoutSession,
        Attribute $attributeEntity,
        array $data
    ) {
        parent::__construct($context, $registry, $data);
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $context->getStoreManager();
        $this->attributeEntity = $attributeEntity;
        $this->eventManager = $context->getEventManager();
    }

    /**
     * get the current order from the checkout session
     * used on the checkout success page
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if ($this->orderLoaded) {
            return $this->order;
        }

        if ($this->order === null) {
            $this->order = $this->checkoutSession->getLastRealOrder();
        }

        if (!$this->order || !$this->order->getId()) {
            $this->order = null;
        }

        $this->orderLoaded = true;
        return $this->order;
    }

    /**
     * check if we should collect order data
     * always true for standard mode
     *
     * @return bool
     */
    public function collectOrders()
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->isExpert()) {
            return $this->getConfig('collect_orders', 'trustbadge');
        }
        return true;
    }

    /**
     * check if we should collect product data
     *
     * @return bool
     */
    public function collectReviews()
    {
        if (!$this->getOrder()) {
            return false;
        }

        if ($this->isExpert()) {
            if (!$this->getConfig('collect_orders', 'trustbadge')) {
                return false;
            }
            return $this->getConfig('expert_collect_reviews', 'product');
        }
        return $this->getConfig('collect_reviews', 'product');
    }

    /**
     * @return string
     */
    public function getIncrementId()
    {
        if (!$this->getOrder()) {
            return '';
        }

        return $this->getOrder()->getIncrementId();
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        if (!$this->getOrder()) {
            return '';
        }

        return $this->getOrder()->getCustomerEmail();
    }

    /**
     * @return string
     */
    public function getOrderAmount()
    {
        if (!$this->getOrder()) {
            return '';
        }

        return $this->getOrder()->getGrandTotal();
    }

    /**
     * @return string
     */
    public function getStoreCurrencyCode()
    {
        if (!$this->getOrder()) {
            return '';
        }

        return $this->getOrder()->getStoreCurrencyCode();
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        if (!$this->getOrder()) {
            return '';
        }

        if (!$this->getOrder()->getPayment()) {
            return '';
        }

        return $this->getOrder()->getPayment()->getMethod();
    }

    /**
     * get the estimated delivery date
     *
     * @return string
     */
    public function getEstimatedDeliveryDate()
    {
        if (!$this->getOrder()) {
            return '';
        }

        $deliveryData = new DataObject();
        $deliveryData->setDate('');
        $this->eventManager->dispatch('trustedshops_trustedshops_get_estimated_delivery_date', [
            'order' => $this->getOrder(),
            'delivery_date' => $deliveryData
        ]);
        return $deliveryData->getDate();
    }

    /**
     * get the product url
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return string
     */
    public function getProductUrl($item)
    {
        $product = $item->getProduct();

        return $product->getProductUrl();
    }

    /**
     * get the product image url
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return string
     */
    public function getProductImage($item)
    {
        $currentStore = $this->storeManager->getStore();
        $mediaBaseUrl = $currentStore->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        $product = $item->getProduct();

        return $mediaBaseUrl . 'catalog/product' . $product->getImage();
    }

    /**
     * get the product name
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return string
     */
    public function getName($item)
    {

        $product = $item->getProduct();
        if ($product->isComposite()) {
            return $product->getName();
        }

        return $item->getName();
    }

    /**
     * get the product sku
     * for composite products get the parents sku
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return string
     */
    public function getProductSkuByItem($item)
    {
        $product = $item->getProduct();
        if ($product->isComposite()) {
            return $product->getSku();
        }

        return $item->getSku();
    }

    /**
     * get the product gtin
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function getProductGTIN($item)
    {
        return $this->_getAttribute($item, 'gtin');
    }

    /**
     * get the product mpn
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function getProductMPN($item)
    {
        return $this->_getAttribute($item, 'mpn');
    }

    /**
     * get the product brand
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function getProductBrand($item)
    {
        return $this->_getAttribute($item, 'brand');
    }

    /**
     * fetch the product attribute depended of the attribute type
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param string $field
     * @return string
     */
    protected function _getAttribute($item, $field)
    {
        // handle different attributes for expert mode
        $prefix = "";
        if ($this->isExpert()) {
            $prefix = 'expert_';
        }

        $attributeCode = $this->getConfig($prefix . 'review_attribute_' . $field, 'product');
        if (empty($attributeCode)) {
            return '';
        }

        /*
         * load attributes of the child product
         */
        $product = $item->getProduct();
        if ($product->isComposite()) {
            $childItems = $item->getChildrenItems();
            $childItem = array_shift($childItems);
            if (!$childItem || !$childItem->getId()) {
                return "";
            }
            $product = $childItem->getProduct();
        }

        if (!$product || !$product->getId()) {
            return "";
        }

        $attribute = $this->getMagentoAttribute($attributeCode);

        switch ($attribute->getFrontendInput()) {
            case 'select':
                $value = $product->getAttributeText($attributeCode);
                break;
            case 'multiselect':
                $value = $product->getAttributeText($attributeCode);
                $value = implode(',', $value);
                break;
            default:
                $storeId = $this->storeManager->getStore()->getId();
                $value = $product->getResource()
                    ->getAttributeRawValue($product->getId(), $attributeCode, $storeId);
                break;
        }
        
        if (is_array($value)) {
            $value = array_shift($value);
        }

        return $this->escapeHtml($value);
    }

    /**
     * load the magento base attribute
     *
     * @param string $attributeCode
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    protected function getMagentoAttribute($attributeCode)
    {
        if (empty($this->attributeCache[$attributeCode])) {
            $this->attributeCache[$attributeCode] = $this->attributeEntity
                ->loadByCode('catalog_product', $attributeCode);
        }

        return $this->attributeCache[$attributeCode];
    }
}
