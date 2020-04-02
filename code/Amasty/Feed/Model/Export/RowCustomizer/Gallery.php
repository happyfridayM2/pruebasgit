<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\UrlInterface;

/**
 * Class Gallery
 */
class Gallery implements RowCustomizerInterface
{
    protected $_storeManager;

    protected $_urlPrefix;

    protected $_gallery = [];

    protected $_export;

    protected $productMetadata;

    protected $resource;

    protected $connection;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\ResourceConnection $resource,
        Product $export
    ) {
        $this->_storeManager = $storeManager;
        $this->_export = $export;
        $this->productMetadata = $productMetadata;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        if ($this->_export->hasAttributes(Product::PREFIX_GALLERY_ATTRIBUTE)) {
            $this->_urlPrefix = $this->_storeManager->getStore($collection->getStoreId())
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . 'catalog/product';

            $this->_gallery = $this->_export->getMediaGallery($productIds);
        }
    }

    /**
     * @return array
     */
    public function getGallery()
    {
        return $this->_gallery;
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
        $productId = $this->convertEntityIdToRowIdIfNeed($productId);
        $customData = &$dataRow['amasty_custom_data'];
        $gallery = $this->getGallery();
        $gallery = isset($gallery[$productId]) ? $gallery[$productId] : [];
        $storeId = 0;
        $galleryImg = array();

        if ($gallery) {
            $storeId = $gallery[0]['_media_store_id'];
        }

        foreach ($gallery as $key => $data) {
            if ($data['_media_store_id'] == $storeId
                && (!isset($customData['image']) || !in_array($this->_urlPrefix . $data['_media_image'], $customData['image']))
            ) {
                $galleryImg[] = $this->_urlPrefix . $data['_media_image'];
            }
        }

        $customData[Product::PREFIX_GALLERY_ATTRIBUTE] = [
            'image_1' => isset($galleryImg[0]) ? $galleryImg[0] : null,
            'image_2' => isset($galleryImg[1]) ? $galleryImg[1] : null,
            'image_3' => isset($galleryImg[2]) ? $galleryImg[2] : null,
            'image_4' => isset($galleryImg[3]) ? $galleryImg[3] : null,
            'image_5' => isset($galleryImg[4]) ? $galleryImg[4] : null,
        ];

        return $dataRow;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }

    /**
     * @param $ids
     * @return array
     */
    protected function convertEntityIdToRowIdIfNeed($ids)
    {
        if ($this->productMetadata->getEdition() == 'Community') {
            return $ids;
        }

        $tableName = $this->resource->getTableName('catalog_product_entity');
        $select = $this->connection->select()
            ->from($tableName, ['row_id'])
            ->where('entity_id IN (?)', $ids);
        $result = $this->connection->fetchCol($select)[0];

        return $result;
    }
}
