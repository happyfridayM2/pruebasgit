<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav as NativeModifier;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Amasty\Conf\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Locator\LocatorInterface;

class Eav
{
    const CONFIGURABLE_GROUP = 'configurable';

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var LocatorInterface
     */
    private $locator;

    function __construct(
        LocatorInterface $locator,
        AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
        $this->locator = $locator;
    }

    /**
     * @param NativeModifier $modifier
     * @param array $meta
     *
     * @return array
     */
    public function afterModifyMeta($modifier, $meta)
    {
        $attribute = $this->getPreselectAttr();
        if ($attribute) {
            $attributeContainer = $modifier->setupAttributeContainerMeta($attribute);
            $attributeContainer =
                $modifier->addContainerChildren($attributeContainer, $attribute, self::CONFIGURABLE_GROUP, 0);
            $attributeContainer['children'][Data::PRESELECT_ATTRIBUTE]['arguments']['data']['config']['notice'] =
                __('Specify child product SKU');
            $meta[self::CONFIGURABLE_GROUP]['children'][NativeModifier::CONTAINER_PREFIX . Data::PRESELECT_ATTRIBUTE] =
                $attributeContainer;

            $meta[self::CONFIGURABLE_GROUP]['arguments']['data']['config']['componentType'] = Fieldset::NAME;
            $meta[self::CONFIGURABLE_GROUP]['arguments']['data']['config']['label'] =
                __('%1', self::CONFIGURABLE_GROUP);
            $meta[self::CONFIGURABLE_GROUP]['arguments']['data']['config']['collapsible'] = true;
            $meta[self::CONFIGURABLE_GROUP]['arguments']['data']['config']['dataScope'] =
                NativeModifier::DATA_SCOPE_PRODUCT;
            $meta[self::CONFIGURABLE_GROUP]['arguments']['data']['config']['sortOrder'] =
                0 * NativeModifier::SORT_ORDER_MULTIPLIER;
        }

        return $meta;
    }

    /**
     * @param $modifier
     * @param $data
     *
     * @return mixed
     */
    public function afterModifyData($modifier, $data)
    {
        $data[$this->locator->getProduct()->getId()][NativeModifier::DATA_SOURCE_DEFAULT][Data::PRESELECT_ATTRIBUTE] =
            $modifier->setupAttributeData($this->getPreselectAttr());

        return $data;
    }

    /**
     * @param $attributeCode
     *
     * @return \Magento\Eav\Api\Data\AttributeInterface|null
     */
    private function getAttribute($attributeCode)
    {
        try {
            $attribute = $this->attributeRepository->get(Product::ENTITY, $attributeCode);
        } catch (NoSuchEntityException $entityException) {
            $attribute = null;
        }

        return $attribute;
    }

    /**
     * @return \Magento\Eav\Api\Data\AttributeInterface|null
     */
    private function getPreselectAttr()
    {
        return $this->getAttribute(Data::PRESELECT_ATTRIBUTE);
    }
}
