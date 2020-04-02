<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Amasty\ShopbyBase\Model\Integration\IntegrationFactory;

/**
 * Class XmlSitemap
 */
class XmlSitemap
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var ObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var \Amasty\ShopbyBase\Helper\Data
     */
    private $baseHelper;

    /**
     * @var IntegrationFactory
     */
    private $integrationFactory;

    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Amasty\ShopbyBase\Helper\Data $baseHelper,
        IntegrationFactory $integrationFactory,
        ObjectFactory $dataObjectFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->baseHelper = $baseHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->integrationFactory = $integrationFactory;
    }

    /**
     * @param string $attrCode
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeByCode($attrCode)
    {
        return $this->eavConfig->getAttribute(Product::ENTITY, $attrCode);
    }

    /**
     * @param $storeId
     * @param null $baseUrl
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBrandUrls($storeId, $baseUrl = null)
    {
        $result = [];
        /** @var \Amasty\ShopbyBrand\Helper\Data|\Amasty\ShopbyBase\Model\Integration\DummyObject $brandHelper */
        $brandHelper = $this->integrationFactory->get(\Amasty\ShopbyBrand\Helper\Data::class, true);

        foreach ($this->getBrandOptions() as $option) {
            if ($option['value']) {
                $url = $brandHelper->getBrandUrl($option);
                if ($baseUrl) {
                    $url = str_replace($baseUrl, '', $url);
                }

                $result[] = $this->dataObjectFactory->create()->setUrl($url);
            }
        }

        return $result;
    }

    /**
     * @return array|\Magento\Eav\Api\Data\AttributeOptionInterface[]|null
     */
    public function getBrandOptions()
    {
        $options = [];
        $attrCode = $this->baseHelper->getBrandAttributeCode();
        if ($attrCode) {
            $brandAttribute = $this->getAttributeByCode($attrCode);
            $options = $brandAttribute->getOptions();
        }

        return $options;
    }
}
