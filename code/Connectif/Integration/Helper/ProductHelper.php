<?php

namespace Connectif\Integration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Helper\Data as TaxHelper;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\Bundle\Model\Product\Type as BundleProduct;
use Magento\Catalog\Model\Category;
use Magento\Review\Model\Rating;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Helper class for Product data.
 */
class ProductHelper extends AbstractHelper
{
    protected $_product;
    protected $_media;
    protected $_logger;
    protected $_taxHelper;
    protected $_productCollection;
    protected $_catalogConfig;
    protected $_configurableProduct;
    protected $_bundleProduct;
    protected $_category;
    protected $_rating;

    /**
     * @param Context $context
     * @param Product $product,
     * @param Config $media,
     * @param TaxHelper $taxHelper,
     * @param Collection $productCollection,
     * @param CatalogConfig $catalogConfig,
     * @param ConfigurableProduct $configurableProduct,
     * @param BundleProduct $bundleProduct,
     * @param Category $category,
     * @param Rating $rating,
     * @param LoggerInterface $logger,
     * @param StockItemRegistryInterface $stockRegistry,
     */
    public function __construct(
        Context $context,
        Product $product,
        Config $media,
        TaxHelper $taxHelper,
        Collection $productCollection,
        CatalogConfig $catalogConfig,
        ConfigurableProduct $configurableProduct,
        BundleProduct $bundleProduct,
        Category $category,
        Rating $rating,
        LoggerInterface $logger,
        ProductRepository $productRepository,
        StockRegistryInterface $stockRegistry
    ) {
        parent::__construct($context);
        $this->_product = $product;
        $this->_media = $media;
        $this->_logger = $logger;
        $this->_taxHelper = $taxHelper;
        $this->_productCollection = $productCollection;
        $this->_catalogConfig = $catalogConfig;
        $this->_configurableProduct = $configurableProduct;
        $this->_bundleProduct = $bundleProduct;
        $this->_category = $category;
        $this->_rating = $rating;
        $this->_productRepository = $productRepository;
        $this->_stockRegistry = $stockRegistry;
    }

    const IN_STOCK = 'instock';
    const OUT_OF_STOCK = 'outofstock';

    public function getProductDataBySKU($productSKU, $onlyMandatoryCartFields = false)
    {
        $product = $this->_productRepository->get($productSKU);
        return $this->getProductData($product, $onlyMandatoryCartFields);
    }

    public function getProductDataById($productId, $onlyMandatoryCartFields = false)
    {
        $product = $this->_product->load($productId);
        return $this->getProductData($product, $onlyMandatoryCartFields);
    }

    public function getProductData($product, $onlyMandatoryCartFields = false)
    {
        $connectifProduct = array();
        // Mandatory fields
        $unitPrice = $this->getProductPrice($product, true, true);
        $connectifProduct['unit_price'] = $unitPrice;
        $connectifProduct['name'] = $product->getName();
        $connectifProduct['url'] = $product->getProductUrl();
        $connectifProduct['product_id'] = $product->getId();

        if (!$onlyMandatoryCartFields) {
            // Optional fields
            $originalUnitPrice = $this->getProductPrice($product, false, true);
            if ($originalUnitPrice == null) {
                $discountedPercentage = null;
                $discountedAmount = null;
            } else {
                if ($originalUnitPrice > 0) {
                    $discountedPercentage = round((($originalUnitPrice - $unitPrice) / $originalUnitPrice) * 100);
                } else {
                    $discountedPercentage = 0;
                }
                $discountedAmount = $originalUnitPrice - $unitPrice;
            }
            $rating = $this->getRating($product);
            $relatedProductIds = $product->getRelatedProductIds();
            if (!$relatedProductIds || !is_array($relatedProductIds)) {
                $relatedProductIds = array();
            }
            $connectifProduct['description'] = $product->getDescription();
            $connectifProduct['image_url'] = $this->_media->getMediaUrl($product->getImage());
            $connectifProduct['availability'] = $this->isInStock($product);
            $connectifProduct['categories'] = $this->getProductCategories($product);
            $connectifProduct['unit_price_original'] = $originalUnitPrice;
            $connectifProduct['unit_price_without_vat'] = $this->getProductPrice($product, true, false);
            $connectifProduct['discounted_percentage'] = $discountedPercentage;
            $connectifProduct['discounted_amount'] = $discountedAmount;
            $connectifProduct['rating_count'] = $rating['rating_count'];
            $connectifProduct['rating_value'] = $rating['rating_stars'];
            // NOTE: We use the same rating_count for review_count because Magento2 does not allow reviews without rating so they are the same
            $connectifProduct['review_count'] = $rating['rating_count'];
            $connectifProduct['relatedProductIds'] = $relatedProductIds;
            $connectifProduct['thumbnail_url'] = $this->_media->getMediaUrl($product->getThumbnail());
            $manufacturer = $product->getAttributeText('manufacturer');
            if ($manufacturer !== false) {
                $connectifProduct['brand'] = $manufacturer;
            }
        }

        return $connectifProduct;
    }

    /**
     * Get if product is in stock
     *
     * @param $product
     * @return string
     */
    private function isInStock($product)
    {
        try {
            $qty = $this->getQty($product);
            if ($qty > 0) {
                return self::IN_STOCK;
            }
            return self::OUT_OF_STOCK;
        } catch (Exception $e) {
            $this->_logger->error($e);
        }
        return self::OUT_OF_STOCK;
    }

    /**
     * Get product stock quantity
     * @param Product $product
     * @return number
     */
    private function getQty($product)
    {
        $qty = 0;
        switch ($product->getTypeId()) {
            case 'grouped':
                $productType = $product->getTypeInstance(true);
                $products = $productType->getAssociatedProducts($product);
                $qty = $this->getLowestStock($products);
                break;
            case 'bundle':
                $bundledItemIds = $this->_bundleProduct
                    ->getChildrenIds($product->getId(), true);
                $products = array();
                foreach ($bundledItemIds as $variants) {
                    if (is_array($variants) && count($variants) > 0) {
                        foreach ($variants as $variantId) {
                            $productModel = $this->_product->load($variantId);
                            $products[] = $productModel;
                        }
                    }
                }
                $qty = $this->getLowestStock($products);
                break;
            case 'configurable':
                $products = $this->_configurableProduct->getUsedProducts($product, null);
                $q = 0;
                foreach ($products as $product) {
                    $q += $this->getQty($product);
                }
                $qty = $q;
                break;
            default:
                $productStock = $this->_stockRegistry->getStockItem($product->getId());
                $qty += $productStock->getQty();
                break;
        }

        return $qty;
    }

    /**
     * Get lowest stock for product collection
     * @param array $productCollection
     * @return number
     */
    private function getLowestStock(array $productCollection)
    {
        $quantities = array();
        $q = 0;
        foreach ($productCollection as $product) {
            $quantities[] = $this->getQty($product);
        }
        if (!empty($quantities)) {
            rsort($quantities, SORT_NUMERIC);
            $q = array_pop($quantities);
        }

        return $q;
    }

    /**
     * Return Product categories
     * @param Product $product
     * @return array
     */
    private function getProductCategories($product)
    {
        $categoryIds = $product->getCategoryIds();
        $productCategories = array();
        foreach ($categoryIds as $id) {
            $cat = $this->_category->load($id);
            $catPath = $this->getCategoryPath($cat, $categoryIds);
            if (!in_array($catPath, $productCategories)) {
                array_push($productCategories, $catPath);
            }
        }
        return $productCategories;
    }

    /**
     * Return category path for category
     * @param string $category
     * @param array $categoryIds
     * @return string
     */
    private function getCategoryPath($category, $categoryIds)
    {
        $categoryPath = "";
        if (in_array($category->getId(), $categoryIds)) {
            $categoryPath = "/" . $category->getName();
        }

        if ($category->getParentId() == 1 || $category->getParentId() == null) {
            return $categoryPath;
        } else {
            $catPath = $this->getCategoryPath($this->_category->load($category->getParentId()), $categoryIds);
            return $catPath . $categoryPath;
        }
    }

    /**
     * Get unit/final price for a product model.
     *
     * @param Product $product the product model.
     * @param bool $finalPrice if final price.
     * @param bool $includeTax if tax is to be included.
     * @return float
     */
    private function getProductPrice(
        Product $product,
        $finalPrice = false,
        $includeTax = true
    ) {
        $price = 0;
        switch ($product->getTypeId()) {
            case Type::TYPE_BUNDLE:
                // NOTE: For bundled products we cannot track unit_price_original,
                // So we just track discounted unit_price with or without vat
                $price = $this->getBundleProductPrices($product, $finalPrice, $includeTax);
                break;
            case 'grouped':
                // NOTE: For grouped products we cannot track unit_price_original,
                // So we just track discounted unit_price with or without vat
                if (!$finalPrice) {
                    $price = null;
                    break;
                }

                // Get the grouped product "starting at" price.
                $tmpProduct = $this->_productCollection->addAttributeToSelect(
                    $this->_catalogConfig->getProductAttributes()
                )
                    ->addAttributeToFilter('entity_id', $product->getId())
                    ->setPage(1, 1)
                    ->addMinimalPrice()
                    ->addTaxPercents()
                    ->load()
                    ->getFirstItem();
                if ($tmpProduct) {
                    $minimalPrice = $tmpProduct->getMinimalPrice();
                    $price = $this->_taxHelper->getTaxPrice($tmpProduct, $minimalPrice, $includeTax);
                } else {
                    $this->_logger->error(sprintf('Error, could not get price for product %s', $product->getName()));
                }
                break;
            case 'configurable':
                // For configurable products we use the price defined for the
                // "parent" product. If for some reason the parent product
                // doesn't have a price configured we fetch the lowest price
                // configured from a child product / variation
                $price = $this->getDefaultProductPrice(
                    $product,
                    $finalPrice,
                    $includeTax
                );
                if (!$price) {
                    $associatedProducts = $this->_configurableProduct->getUsedProducts($product, null);
                    $lowestPrice = false;
                    foreach ($associatedProducts as $associatedProduct) {
                        $this->_product->load(
                            $associatedProduct->getId()
                        );
                        if ($this->_product && $this->_product->isAvailable()) {
                            $variationPrice = $this->getProductPrice($this->_product, $finalPrice, $includeTax);
                            if (!$lowestPrice || $variationPrice < $lowestPrice) {
                                $lowestPrice = $variationPrice;
                            }
                        }
                    }
                    $price = $lowestPrice;
                }
                break;
            default:
                $price = $this->getDefaultProductPrice(
                    $product,
                    $finalPrice,
                    $includeTax
                );
                break;
        }
        return $price;
    }

    /**
     * @param Product $product
     * @param bool $finalPrice
     * @param bool $includeTax
     * @return float
     */
    private function getBundleProductPrices(
        Product $product,
        $finalPrice = false,
        $includeTax = true
    ) {
        $fixedPrice = $this->getDefaultProductPrice($product, $finalPrice, $includeTax);
        if (is_numeric($fixedPrice) && $fixedPrice > 0) {
            return $fixedPrice;
        }
        if (!$finalPrice) {
            return null;
        }
        $model = $product->getPriceModel();
        $minBundlePrice = $model->getTotalPrices($product, 'min', $includeTax, $finalPrice);
        return $minBundlePrice;
    }

    /**
     * Returns the price from product
     *
     * @param Product $product
     * @param bool $finalPrice
     * @param bool $includeTax
     * @return float
     */
    private function getDefaultProductPrice(
        Product $product,
        $finalPrice = false,
        $includeTax = true
    ) {
        if ($finalPrice) {
            $price = $this->_taxHelper->getTaxPrice($product, $product->getFinalPrice(), $includeTax);
        } else {
            $price = $this->_taxHelper->getTaxPrice($product, $product->getPrice(), $includeTax);
        }
        return $price;
    }

    /**
     * Get rating for a product.
     *
     * @param Product $product the product model.
     * @return object
     */
    private function getRating(
        Product $product
    ) {
        $ratingInfo = array();
        $ratingObject = $this->_rating->getEntitySummary($product->getId());
        $ratingCount = (int)$ratingObject->getCount();
        if ($ratingCount > 0) {
            $ratingPercentage = $ratingObject->getSum() / $ratingCount;
        } else {
            $ratingPercentage = 0;
        }
        // NOTE: We assume max stars are 5 so we get the number of stars
        $ratingStars = $ratingPercentage * 5 / 100;
        $ratingInfo['rating_stars'] = $ratingStars;
        $ratingInfo['rating_count'] = $ratingCount;
        return $ratingInfo;
    }
}
