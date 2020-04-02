<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product;
use Magento\Framework\UrlInterface;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Sitemap\Model\ResourceModel\Catalog\Product as ProductSitemap;

/**
 * Class Image
 */
class Image implements RowCustomizerInterface
{
    const THUMBNAIL_TYPE = 'thumbnail';

    const IMAGE_TYPE = 'image';

    const SMALL_IMAGE_TYPE = 'small_image';

    protected $_storeManager;

    protected $_urlPrefix;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        $this->_urlPrefix = $this->_storeManager->getStore($collection->getStoreId())
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . 'catalog/product';
    }

    /**
     * @inheritdoc
     */
    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow['amasty_custom_data'];

        $customData[Product::PREFIX_IMAGE_ATTRIBUTE] = [
            self::THUMBNAIL_TYPE => $this->checkImage($dataRow, self::THUMBNAIL_TYPE),
            self::IMAGE_TYPE => $this->checkImage($dataRow, self::IMAGE_TYPE),
            self::SMALL_IMAGE_TYPE => $this->checkImage($dataRow, self::SMALL_IMAGE_TYPE),
        ];

        return $dataRow;
    }

    /**
     * @param array $dataRow
     * @param string $imgType
     *
     * @return string|null
     */
    private function checkImage($dataRow, $imgType)
    {
        if (isset($dataRow[$imgType])
            && $dataRow[$imgType] !== ProductSitemap::NOT_SELECTED_IMAGE
        ) {
            return $this->_urlPrefix . $dataRow[$imgType];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }
}
