<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Amasty\Feed\Model\Export\Product as ExportProduct;
use Amasty\Feed\Model\Export\RowCustomizer\Advanced;
use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Class Content
 *
 * @package Amasty\Feed
 */
class Content extends Widget implements RendererInterface
{
    /**#@+
     * Keys for collection data array
     */
    const CODE = 'code';

    const NAME = 'name';
    /**#@-*/

    /**#@+
     * Modifiers
     */
    const STRIP_TAGS = 'strip_tags';

    const HTML_ESCAPE = 'html_escape';

    const UPPERCASE = 'uppercase';

    const CAPITALIZE = 'capitalize';

    const LOWERCASE = 'lowercase';

    const INTEGER = 'integer';

    const LENGTH = 'length';

    const PREPEND = 'prepend';

    const APPEND = 'append';

    const REPLACE = 'replace';

    const ROUND = 'round';

    const IF_EMPTY = 'if_empty';

    const IF_NOT_EMPTY = 'if_not_empty';

    const FULL_IF_NOT_EMPTY = 'full_if_not_empty';

    const TO_SECURE_URL = 'to_secure_url';

    const TO_UNSECURE_URL = 'to_unsecure_url';
    /**#@-*/

    /**
     * @var ExportProduct
     */
    protected $_export;

    /**
     * @var \Amasty\Feed\Model\Field\ResourceModel\Collection
     */
    private $fieldCollection;

    /**
     * @var \Amasty\Feed\Model\Category\ResourceModel\Collection
     */
    private $categoryCollection;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ExportProduct $export,
        \Amasty\Feed\Model\Category\ResourceModel\CollectionFactory $categoryCollectionFactory,
        \Amasty\Feed\Model\Field\ResourceModel\CollectionFactory $fieldCollection,
        array $data = []
    ) {
        $this->_export = $export;
        $this->fieldCollection = $fieldCollection->create();

        parent::__construct($context, $data);
        $this->categoryCollection = $categoryCollectionFactory->create()->addOrder('name');
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);

        return $this->toHtml();
    }

    /**
     * @return array
     */
    public function getFormats()
    {
        return [
            'as_is' => 'As Is',
            'date' => 'Date',
            'price' => 'Price',
        ];
    }

    /**
     * @return array
     */
    public function getYesNoVars()
    {
        return [
            'no' => 'No',
            'yes' => 'Yes',
        ];
    }

    /**
     * @return array
     */
    public function getInventoryAttributes()
    {
        // all inventory qty,min_qty,use_config_min_qty,is_qty_decimal,backorders,use_config_backorders,min_sale_qty,
        // use_config_min_sale_qty,max_sale_qty,use_config_max_sale_qty,is_in_stock,notify_stock_qty,
        // use_config_notify_stock_qty,manage_stock,use_config_manage_stock,use_config_qty_increments,qty_increments,
        // use_config_enable_qty_inc,enable_qty_increments,is_decimal_divided,website_id

        $attributes = [
            'qty' => 'Qty',
            'is_in_stock' => 'Is In Stock',
        ];

        return $this->customizeArray($attributes, ExportProduct::PREFIX_INVENTORY_ATTRIBUTE);
    }

    /**
     * @return array
     */
    public function getBasicAttributes()
    {
        $attributes = [
            'sku' => 'SKU',
            'product_type' => 'Type',
            'product_websites' => 'Websites',
            'created_at' => 'Created',
            'updated_at' => 'Updated',
            'product_id' => 'Product ID',
        ];

        return $this->customizeArray($attributes, ExportProduct::PREFIX_BASIC_ATTRIBUTE);
    }

    /**
     * @return array
     */
    public function getAdvancedAttributes()
    {
        return $this->customizeArray(Advanced::ATTRIBUTES, ExportProduct::PREFIX_ADVANCED_ATTRIBUTE);
    }

    /**
     * @return array
     */
    public function getCategoryAttributes()
    {
        $attributes[$this->getCode('category', ExportProduct::PREFIX_CATEGORY_ATTRIBUTE)]
            = $this->getTitle('Default', 'category');
        $attributes += $this->customizeArray(
            $this->categoryCollection->getData(),
            ExportProduct::PREFIX_MAPPED_CATEGORY_ATTRIBUTE,
            false
        );

        return $attributes;
    }

    /**
     * @return array
     */
    public function getCategoryPathsAttributes()
    {
        $attributes[$this->getCode('category', ExportProduct::PREFIX_CATEGORY_PATH_ATTRIBUTE)]
            = $this->getTitle('Default', 'category');

        $attributes += $this->customizeArray(
            $this->categoryCollection->getData(),
            ExportProduct::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE,
            false
        );

        return $attributes;
    }

    /**
     * @return array
     */
    public function getCustomFieldsAttributes()
    {
        return $this->customizeArray(
            $this->fieldCollection->getSortedCollection()->getData(),
            ExportProduct::PREFIX_CUSTOM_FIELD_ATTRIBUTE,
            false
        );
    }

    /**
     * @return array
     */
    public function getImageAttributes()
    {
        $attributes = [
            'thumbnail' => 'Thumbnail',
            'image' => 'Base Image',
            'small_image' => 'Small Image',
        ];

        return $this->customizeArray($attributes, ExportProduct::PREFIX_IMAGE_ATTRIBUTE);
    }

    /**
     * @return array
     */
    public function getGalleryAttributes()
    {
        $attributes = [
            'image_1' => 'Image 1',
            'image_2' => 'Image 2',
            'image_3' => 'Image 3',
            'image_4' => 'Image 4',
            'image_5' => 'Image 5',
        ];

        return $this->customizeArray($attributes, ExportProduct::PREFIX_GALLERY_ATTRIBUTE);
    }

    /**
     * @return array
     */
    public function getPriceAttributes()
    {
        $attributes = [
            'price' => 'Price',
            'final_price' => 'Final Price',
            'min_price' => 'Min Price',
            'max_price' => 'Max Price',
            'tax_price' => 'Price with TAX(VAT)',
            'tax_final_price' => 'Final Price with TAX(VAT)',
            'tax_min_price' => 'Min Price with TAX(VAT)',
        ];

        return $this->customizeArray($attributes, ExportProduct::PREFIX_PRICE_ATTRIBUTE);
    }

    /**
     * @return array
     */
    public function getUrlAttributes()
    {
        $attributes = [
            'short' => 'Short',
            'with_category' => 'With Category',
        ];

        return $this->customizeArray($attributes, ExportProduct::PREFIX_URL_ATTRIBUTE);
    }

    /**
     * @return array
     */
    public function getProductAttributes()
    {
        return $this->customizeArray($this->_export->getExportAttrCodesList(), ExportProduct::PREFIX_PRODUCT_ATTRIBUTE);
    }

    /**
     * @param string $title
     * @param string $code
     *
     * @return string
     */
    private function getTitle($title, $code)
    {
        return $title . ' [' . $code . ']';
    }

    /**
     * @param string $code
     * @param string $type
     *
     * @return string
     */
    private function getCode($code, $type)
    {
        return $type . '|' . $code;
    }

    /**
     * @param array $array
     * @param string $type
     * @param bool $useKey
     *
     * @return array
     */
    private function customizeArray($array, $type, $useKey = true)
    {
        if (!$array) {
            return [];
        }

        $result = [];

        if ($useKey) {
            foreach ($array as $code => $title) {
                $result[$this->getCode($code, $type)] = $this->getTitle($title, $code);
            }
        } else {
            foreach ($array as $item) {
                $result[$this->getCode($item[self::CODE], $type)]
                    = $this->getTitle($item[self::NAME], $item[self::CODE]);
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getModiftVars()
    {
        $modifiers = [
            self::STRIP_TAGS => __('Strip Tags'),
            self::HTML_ESCAPE => __('Html Escape'),
            self::UPPERCASE => __('Uppercase'),
            self::CAPITALIZE => __('Capitalize'),
            self::LOWERCASE => __('Lowercase'),
            self::INTEGER => __('Integer'),
            self::LENGTH => __('Length'),
            self::PREPEND => __('Prepend'),
            self::APPEND => __('Append'),
            self::REPLACE => __('Replace'),
            self::ROUND => __('Round'),
            self::IF_EMPTY => __('If Empty'),
            self::IF_NOT_EMPTY => __('If Not Empty'),
            self::FULL_IF_NOT_EMPTY => __('If Empty/Not Empty'),
            self::TO_SECURE_URL => __('To secure URL'),
            self::TO_UNSECURE_URL => __('To unsecure URL'),
        ];

        return $modifiers;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        $args = [
            'replace' => [
                __('From'),
                __('To'),
            ],
            'prepend' => [
                __('Text'),
            ],
            'if_empty' => [
                __('Text'),
            ],
            'if_not_empty' => [
                __('Text'),
            ],
            'full_if_not_empty' => [
                __('Empty'),
                __('Not Empty'),
            ],
            'append' => [
                __('Text'),
            ],
            'length' => [
                __('Max Length'),
            ],
        ];

        foreach ($args as $index => $value) {
            $args[$this->escapeHtml($index)] = $this->escapeHtml($value);
        }

        return $args;
    }

    /**
     * @param string $text
     *
     * @return array|string
     */
    public function escapeOption($text)
    {
        return $this->escapeJsQuote($this->escapeHtml($text));
    }
}
