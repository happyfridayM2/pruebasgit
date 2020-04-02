<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Composite;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\Conf\Helper\Data;

class ConfigurablePanel
{
    const CONFIGURABLE_GROUP = 'configurable';

    private $actionsList = 'Amasty_Conf/components/actions-list';

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var Eav
     */
    private $eavModifier;

    public function __construct(
        LocatorInterface $locator,
        AttributeRepositoryInterface $attributeRepository,
        Eav $eavModifier
    ) {
        $this->locator = $locator;
        $this->attributeRepository = $attributeRepository;
        $this->eavModifier = $eavModifier;
    }

    /**
     * @param \Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePanel $subject
     * @param $meta
     * @return array
     */
    public function afterModifyMeta($subject, $meta)
    {
        $attribute = $this->getPreselectAttr();
        if ($attribute) {
            $attributeContainer = $this->eavModifier->addContainerChildren(
                $this->eavModifier->setupAttributeContainerMeta($attribute),
                $attribute,
                self::CONFIGURABLE_GROUP,
                0
            );
            $attributeContainer['arguments']['data']['config']['dataScope'] = Eav::DATA_SCOPE_PRODUCT;
            $attributeContainer['children'][Data::PRESELECT_ATTRIBUTE]['arguments']['data']['config']['notice'] =
                __('Specify child product SKU');
            $meta[self::CONFIGURABLE_GROUP]['children'][Eav::CONTAINER_PREFIX . Data::PRESELECT_ATTRIBUTE] =
                $attributeContainer;

            /** @var array $actionListConfig Add preselect button in dropdown */
            $actionListConfig = &$meta[self::CONFIGURABLE_GROUP]['children']['configurable-matrix']['children']['record']['children']['actionsList']['arguments']['data']['config'];// phpcs:ignore
            $actionListConfig['component'] = 'amasty_preselect';
            $actionListConfig['template'] = $this->actionsList;
        }

        return $meta;
    }
    /**
     * @param $modifier
     * @param array $data
     *
     * @return array
     */
    public function afterModifyData($modifier, $data)
    {
        $data[$this->locator->getProduct()->getId()][Composite::DATA_SOURCE_DEFAULT][Data::PRESELECT_ATTRIBUTE] =
            $this->eavModifier->setupAttributeData($this->getPreselectAttr());

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
