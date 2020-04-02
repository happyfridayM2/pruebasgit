<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

use Amasty\Feed\Api\Data\FeedInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Feed
 */
class Feed extends AbstractModel implements FeedInterface
{
    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Feed\Model\ResourceModel\Feed $resource,
        \Amasty\Feed\Model\ResourceModel\Feed\Collection $resourceCollection,
        \Amasty\Base\Model\Serializer $serializer,
        array $data = []
    ) {
        $this->serializer = $serializer;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        $this->_init(\Amasty\Feed\Model\ResourceModel\Feed::class);
        $this->setIdFieldName('entity_id');
    }

    /**
     * @return bool
     */
    public function isCsv()
    {
        return $this->getFeedType() == 'txt' || $this->getFeedType() == 'csv';
    }

    /**
     * @return bool
     */
    public function isXml()
    {
        return $this->getFeedType() == 'xml';
    }

    /**
     * @inheritdoc
     */
    public function getCsvField()
    {
        $ret = $this->getData('csv_field');

        if (!is_array($ret)) {
            $config = $this->serializer->unserialize($ret);
            $ret = [];

            if (is_array($config)) {
                foreach ($config as $item) {
                    $ret[] = [
                        'header' => isset($item['header']) ? $item['header'] : '',
                        'attribute' => isset($item['attribute']) ? $item['attribute'] : null,
                        'static_text' => isset($item['static_text']) ? $item['static_text'] : null,
                        'format' => isset($item['format']) ? $item['format'] : '',
                        'parent' => isset($item['parent']) ? $item['parent'] : '',
                        'modify' => isset($item['modify']) ? $item['modify'] : [],
                    ];
                }
            }
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function getUtmParams()
    {
        $ret = [];

        if ($this->getUtmSource()) {
            $ret['utm_source'] = $this->getUtmSource();
        }

        if ($this->getUtmMedium()) {
            $ret['utm_medium'] = $this->getUtmMedium();
        }

        if ($this->getUtmTerm()) {
            $ret['utm_term'] = $this->getUtmTerm();
        }

        if ($this->getUtmContent()) {
            $ret['utm_content'] = $this->getUtmContent();
        }

        if ($this->getUtmCampaign()) {
            $ret['utm_campaign'] = $this->getUtmCampaign();
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function getFilename()
    {
        $ret = $this->_getData(FeedInterface::FILENAME);
        $ext = '.' . $this->getFeedType();

        if (strpos($ret, $ext) === false) {
            $ret .= $ext;
        }

        return $ret;
    }

    public function getConditionsSerialized()
    {
        $conditionsSerialized = $this->getData('conditions_serialized');

        if ($conditionsSerialized) {
            if ($conditionsSerialized[0] == 'a') { // Old serialization format used
                // New version of Magento
                if (interface_exists(\Magento\Framework\Serialize\SerializerInterface::class)) {
                    $conditionsSerialized = $this->serializer->serialize(
                        $this->serializer->unserialize($conditionsSerialized)
                    );
                }
            }
        }

        return $conditionsSerialized;
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->_getData(FeedInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(FeedInterface::ENTITY_ID, $entityId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData(FeedInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(FeedInterface::NAME, $name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFilename($filename)
    {
        $this->setData(FeedInterface::FILENAME, $filename);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFeedType()
    {
        return $this->_getData(FeedInterface::FEED_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setFeedType($feedType)
    {
        $this->setData(FeedInterface::FEED_TYPE, $feedType);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIsActive()
    {
        return $this->_getData(FeedInterface::IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setIsActive($isActive)
    {
        $this->setData(FeedInterface::IS_ACTIVE, $isActive);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->_getData(FeedInterface::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        $this->setData(FeedInterface::STORE_ID, $storeId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExecuteMode()
    {
        return $this->_getData(FeedInterface::EXECUTE_MODE);
    }

    /**
     * @inheritdoc
     */
    public function setExecuteMode($executeMode)
    {
        $this->setData(FeedInterface::EXECUTE_MODE, $executeMode);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCronTime()
    {
        return $this->_getData(FeedInterface::CRON_TIME);
    }

    /**
     * @inheritdoc
     */
    public function setCronTime($cronTime)
    {
        $this->setData(FeedInterface::CRON_TIME, $cronTime);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCsvColumnName()
    {
        return $this->_getData(FeedInterface::CSV_COLUMN_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setCsvColumnName($csvColumnName)
    {
        $this->setData(FeedInterface::CSV_COLUMN_NAME, $csvColumnName);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCsvHeader()
    {
        return $this->_getData(FeedInterface::CSV_HEADER);
    }

    /**
     * @inheritdoc
     */
    public function setCsvHeader($csvHeader)
    {
        $this->setData(FeedInterface::CSV_HEADER, $csvHeader);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCsvEnclosure()
    {
        return $this->_getData(FeedInterface::CSV_ENCLOSURE);
    }

    /**
     * @inheritdoc
     */
    public function setCsvEnclosure($csvEnclosure)
    {
        $this->setData(FeedInterface::CSV_ENCLOSURE, $csvEnclosure);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCsvDelimiter()
    {
        return $this->_getData(FeedInterface::CSV_DELIMITER);
    }

    /**
     * @inheritdoc
     */
    public function setCsvDelimiter($csvDelimiter)
    {
        $this->setData(FeedInterface::CSV_DELIMITER, $csvDelimiter);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFormatPriceCurrency()
    {
        return $this->_getData(FeedInterface::FORMAT_PRICE_CURRENCY);
    }

    /**
     * @inheritdoc
     */
    public function setFormatPriceCurrency($formatPriceCurrency)
    {
        $this->setData(FeedInterface::FORMAT_PRICE_CURRENCY, $formatPriceCurrency);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCsvField($csvField)
    {
        $this->setData(FeedInterface::CSV_FIELD, $csvField);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getXmlHeader()
    {
        return $this->_getData(FeedInterface::XML_HEADER);
    }

    /**
     * @inheritdoc
     */
    public function setXmlHeader($xmlHeader)
    {
        $this->setData(FeedInterface::XML_HEADER, $xmlHeader);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getXmlItem()
    {
        return $this->_getData(FeedInterface::XML_ITEM);
    }

    /**
     * @inheritdoc
     */
    public function setXmlItem($xmlItem)
    {
        $this->setData(FeedInterface::XML_ITEM, $xmlItem);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getXmlContent()
    {
        return $this->_getData(FeedInterface::XML_CONTENT);
    }

    /**
     * @inheritdoc
     */
    public function setXmlContent($xmlContent)
    {
        $this->setData(FeedInterface::XML_CONTENT, $xmlContent);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getXmlFooter()
    {
        return $this->_getData(FeedInterface::XML_FOOTER);
    }

    /**
     * @inheritdoc
     */
    public function setXmlFooter($xmlFooter)
    {
        $this->setData(FeedInterface::XML_FOOTER, $xmlFooter);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFormatPriceCurrencyShow()
    {
        return $this->_getData(FeedInterface::FORMAT_PRICE_CURRENCY_SHOW);
    }

    /**
     * @inheritdoc
     */
    public function setFormatPriceCurrencyShow($formatPriceCurrencyShow)
    {
        $this->setData(FeedInterface::FORMAT_PRICE_CURRENCY_SHOW, $formatPriceCurrencyShow);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFormatPriceDecimals()
    {
        return $this->_getData(FeedInterface::FORMAT_PRICE_DECIMALS);
    }

    /**
     * @inheritdoc
     */
    public function setFormatPriceDecimals($formatPriceDecimals)
    {
        $this->setData(FeedInterface::FORMAT_PRICE_DECIMALS, $formatPriceDecimals);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFormatPriceDecimalPoint()
    {
        return $this->_getData(FeedInterface::FORMAT_PRICE_DECIMAL_POINT);
    }

    /**
     * @inheritdoc
     */
    public function setFormatPriceDecimalPoint($formatPriceDecimalPoint)
    {
        $this->setData(FeedInterface::FORMAT_PRICE_DECIMAL_POINT, $formatPriceDecimalPoint);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFormatPriceThousandsSeparator()
    {
        return $this->_getData(FeedInterface::FORMAT_PRICE_THOUSANDS_SEPARATOR);
    }

    /**
     * @inheritdoc
     */
    public function setFormatPriceThousandsSeparator($formatPriceThousandsSeparator)
    {
        $this->setData(FeedInterface::FORMAT_PRICE_THOUSANDS_SEPARATOR, $formatPriceThousandsSeparator);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFormatDate()
    {
        return $this->_getData(FeedInterface::FORMAT_DATE);
    }

    /**
     * @inheritdoc
     */
    public function setFormatDate($formatDate)
    {
        $this->setData(FeedInterface::FORMAT_DATE, $formatDate);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setConditionsSerialized($conditionsSerialized)
    {
        $this->setData(FeedInterface::CONDITIONS_SERIALIZED, $conditionsSerialized);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGeneratedAt()
    {
        return $this->_getData(FeedInterface::GENERATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setGeneratedAt($generatedAt)
    {
        $this->setData(FeedInterface::GENERATED_AT, $generatedAt);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryEnabled()
    {
        return $this->_getData(FeedInterface::DELIVERY_ENABLED);
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryEnabled($deliveryEnabled)
    {
        $this->setData(FeedInterface::DELIVERY_ENABLED, $deliveryEnabled);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryHost()
    {
        return $this->_getData(FeedInterface::DELIVERY_HOST);
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryHost($deliveryHost)
    {
        $this->setData(FeedInterface::DELIVERY_HOST, $deliveryHost);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryType()
    {
        return $this->_getData(FeedInterface::DELIVERY_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryType($deliveryType)
    {
        $this->setData(FeedInterface::DELIVERY_TYPE, $deliveryType);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryUser()
    {
        return $this->_getData(FeedInterface::DELIVERY_USER);
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryUser($deliveryUser)
    {
        $this->setData(FeedInterface::DELIVERY_USER, $deliveryUser);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryPassword()
    {
        return $this->_getData(FeedInterface::DELIVERY_PASSWORD);
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryPassword($deliveryPassword)
    {
        $this->setData(FeedInterface::DELIVERY_PASSWORD, $deliveryPassword);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryPath()
    {
        return $this->_getData(FeedInterface::DELIVERY_PATH);
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryPath($deliveryPath)
    {
        $this->setData(FeedInterface::DELIVERY_PATH, $deliveryPath);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryPassiveMode()
    {
        return $this->_getData(FeedInterface::DELIVERY_PASSIVE_MODE);
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryPassiveMode($deliveryPassiveMode)
    {
        $this->setData(FeedInterface::DELIVERY_PASSIVE_MODE, $deliveryPassiveMode);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUtmSource()
    {
        return $this->_getData(FeedInterface::UTM_SOURCE);
    }

    /**
     * @inheritdoc
     */
    public function setUtmSource($utmSource)
    {
        $this->setData(FeedInterface::UTM_SOURCE, $utmSource);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUtmMedium()
    {
        return $this->_getData(FeedInterface::UTM_MEDIUM);
    }

    /**
     * @inheritdoc
     */
    public function setUtmMedium($utmMedium)
    {
        $this->setData(FeedInterface::UTM_MEDIUM, $utmMedium);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUtmTerm()
    {
        return $this->_getData(FeedInterface::UTM_TERM);
    }

    /**
     * @inheritdoc
     */
    public function setUtmTerm($utmTerm)
    {
        $this->setData(FeedInterface::UTM_TERM, $utmTerm);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUtmContent()
    {
        return $this->_getData(FeedInterface::UTM_CONTENT);
    }

    /**
     * @inheritdoc
     */
    public function setUtmContent($utmContent)
    {
        $this->setData(FeedInterface::UTM_CONTENT, $utmContent);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUtmCampaign()
    {
        return $this->_getData(FeedInterface::UTM_CAMPAIGN);
    }

    /**
     * @inheritdoc
     */
    public function setUtmCampaign($utmCampaign)
    {
        $this->setData(FeedInterface::UTM_CAMPAIGN, $utmCampaign);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIsTemplate()
    {
        return $this->_getData(FeedInterface::IS_TEMPLATE);
    }

    /**
     * @inheritdoc
     */
    public function setIsTemplate($isTemplate)
    {
        $this->setData(FeedInterface::IS_TEMPLATE, $isTemplate);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompress()
    {
        return $this->_getData(FeedInterface::COMPRESS);
    }

    /**
     * @inheritdoc
     */
    public function setCompress($compress)
    {
        $this->setData(FeedInterface::COMPRESS, $compress);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExcludeDisabled()
    {
        return $this->_getData(FeedInterface::EXCLUDE_DISABLED);
    }

    /**
     * @inheritdoc
     */
    public function setExcludeDisabled($excludeDisabled)
    {
        $this->setData(FeedInterface::EXCLUDE_DISABLED, $excludeDisabled);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExcludeOutOfStock()
    {
        return $this->_getData(FeedInterface::EXCLUDE_OUT_OF_STOCK);
    }

    /**
     * @inheritdoc
     */
    public function setExcludeOutOfStock($excludeOutOfStock)
    {
        $this->setData(FeedInterface::EXCLUDE_OUT_OF_STOCK, $excludeOutOfStock);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExcludeNotVisible()
    {
        return $this->_getData(FeedInterface::EXCLUDE_NOT_VISIBLE);
    }

    /**
     * @inheritdoc
     */
    public function setExcludeNotVisible($excludeNotVisible)
    {
        $this->setData(FeedInterface::EXCLUDE_NOT_VISIBLE, $excludeNotVisible);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCronDay()
    {
        return $this->_getData(FeedInterface::CRON_DAY);
    }

    /**
     * @inheritdoc
     */
    public function setCronDay($cronDay)
    {
        $this->setData(FeedInterface::CRON_DAY, $cronDay);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductsAmount()
    {
        return $this->_getData(FeedInterface::PRODUCTS_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setProductsAmount($productsAmount)
    {
        $this->setData(FeedInterface::PRODUCTS_AMOUNT, $productsAmount);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGenerationType()
    {
        return $this->_getData(FeedInterface::GENERATION_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setGenerationType($generationType)
    {
        $this->setData(FeedInterface::GENERATION_TYPE, $generationType);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(FeedInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(FeedInterface::STATUS, $status);

        return $this;
    }
}
