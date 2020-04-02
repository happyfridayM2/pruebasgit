<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\Registry;
use Magmodules\AlternateHreflang\Helper\General as GeneralHelper;
use Magento\Framework\App\Area;
use Magento\Store\Model\App\Emulation;
use Psr\Log\LoggerInterface;

class Product extends AbstractHelper
{

    /**
     * @var General
     */
    private $generalHelper;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * Product constructor.
     *
     * @param Context           $context
     * @param ProductRepository $productRepository
     * @param ProductHelper     $productHelper
     * @param General           $generalHelper
     * @param Registry          $registry
     * @param Emulation         $appEmulation
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        ProductHelper $productHelper,
        GeneralHelper $generalHelper,
        Registry $registry,
        Emulation $appEmulation
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->generalHelper = $generalHelper;
        $this->registry = $registry;
        $this->productHelper = $productHelper;
        $this->logger = $context->getLogger();
        $this->appEmulation = $appEmulation;
    }

    /**
     * Returns all alternate Product url's in array
     *
     * @return array|bool
     */
    public function getAlternateData()
    {
        if ($this->generalHelper->getEnabled('product')) {
            $alternateData = [];
            $storeId = $this->generalHelper->getCurrentStore();
            $targetData = $this->generalHelper->getTargetData($storeId);

            if (empty($targetData['group_id'])) {
                return false;
            }

            $groupId = $targetData['group_id'];
            $product = $this->getCurrentProduct();
            $currentUrl = $this->generalHelper->getCurrentUrl(true);
            $canonical = $this->generalHelper->getCanonicalEnabled();
            $canonicalCheck = $this->getCanonicalCheck($product, $currentUrl);

            if ($canonical) {
                if (empty($canonicalCheck)) {
                    $alternateData['error'] = __('Current product URL is not the canonical URL.');
                    return $alternateData;
                }
            }

            foreach ($targetData[$groupId] as $row) {
                if ($storeId != $row['store_id']) {
                    $url = $this->getProductUrlByStore($product, $row['store_id'], $canonicalCheck);
                    if ($url) {
                        $languageCode = $row['language_code'];
                        $alternateData['urls'][$languageCode] = $url;
                    }
                } else {
                    $url = $this->getProductUrlByStore($product, $row['store_id'], $canonicalCheck);
                    $languageCode = $row['language_code'];
                    $alternateData['urls'][$languageCode] = $url;
                }
            }

            if (empty($alternateData['urls'])) {
                $alternateData['error'] = __('No Alternate URLs found.');
                return $alternateData;
            }

            if (count($alternateData['urls']) == 1) {
                $alternateData['error'] = __(
                    'Only one Alternate URL Found (%1). Needs at least two.',
                    implode('', $alternateData['urls'])
                );
                return $alternateData;
            }

            $canonical = $this->generalHelper->getCanonicalEnabled();
            $currentUrl = $this->generalHelper->getCurrentUrl(true);
            if (isset($currentAlternate) && $canonical && $currentAlternate != $currentUrl) {
                $alternateData['error'] = __(
                    'Current URL %1 not canonical. Canonical: %2.',
                    $currentUrl,
                    $currentAlternate
                );
                return $alternateData;
            }

            return $alternateData;
        }

        if ($this->generalHelper->getAlternateDebug()) {
            $alternateData['error'] = __('Product Alternate Data not enabled.');
            return $alternateData;
        }

        return false;
    }

    /**
     * Load current product from registry
     *
     * @return ProductInterface
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Checks if current Product is Canonical Url (= witouth categoies)
     *
     * @param ProductInterface $product
     * @param                  $currentUrl
     *
     * @return bool
     */
    public function getCanonicalCheck($product, $currentUrl)
    {
        $canonical = $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
        if ($canonical != $currentUrl) {
            return false;
        }

        return true;
    }

    /**
     * Get Product Url by StoreId
     *
     * @param ProductInterface $product
     * @param                  $storeId
     * @param                  $ignoreCategory
     *
     * @return null|string
     */
    public function getProductUrlByStore($product, $storeId, $ignoreCategory)
    {
        $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);

        try {
            $productStore = $this->productRepository->getById($product->getId(), true, $storeId);
            if ($this->validateproduct($productStore, $storeId)) {
                $url = $productStore->getUrlModel()->getUrl(
                    $productStore,
                    ['_ignore_category' => $ignoreCategory, '_scope' => $storeId]
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        $this->appEmulation->stopEnvironmentEmulation();

        return isset($url) ? strtok($url, '?') : null;
    }

    /**
     * Validate product visibility and website
     *
     * @param ProductInterface $product
     * @param                  $storeId
     *
     * @return bool
     */
    private function validateproduct($product, $storeId)
    {
        if ($product->getStatus() == 2 || $product->getVisibility() == 1) {
            return false;
        }

        $websiteIds = $product->getWebsiteIds();
        if (!in_array($this->generalHelper->getWebsiteIdFromStoreId($storeId), $websiteIds)) {
            return false;
        }

        return true;
    }

}
