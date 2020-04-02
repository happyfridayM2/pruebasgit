<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\Eav\Model\Entity\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend as DefaultBackendModel;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Amasty\Conf\Helper\Data;
use Magento\Framework\DataObject;
use \Magento\Eav\Model\Entity\Attribute\Exception as AttributeException;
use \Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

class DefaultBackend
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DataObject
     */
    private $productObject;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param DefaultBackendModel $subject
     * @param DataObject $object
     * @return array
     */
    public function beforeValidate($subject, $object)
    {
        if ($subject->getAttribute()->getAttributeCode() == Data::PRESELECT_ATTRIBUTE) {
            $this->setProductObject(
                $object
            );
        }

        return [$object];
    }

    /**
     * @param DefaultBackendModel $subject
     * @param boolean $validated
     * @return bool
     */
    public function afterValidate($subject, $validated)
    {
        if ($subject->getAttribute()->getAttributeCode() == Data::PRESELECT_ATTRIBUTE && $validated) {
            try {
                $object = $this->getProductObject();
                $skuSimple = $object->getData(Data::PRESELECT_ATTRIBUTE);
                if ($skuSimple) {
                    $simple = $this->productRepository->get($skuSimple);
                    if (!in_array(
                        $object->getId(),
                        $object->getTypeInstance()->getParentIdsByChild($simple->getId())
                    )) {
                        $this->throwException(__(
                            '"%1" SKU isn\'t associated with this configurable product',
                            $skuSimple
                        ));
                        $validated = false;
                    }
                }
            } catch (NoSuchEntityException $e) {
                $validated = false;
                $this->throwException(__('Please enter valid product SKU into preselect field'));
            }
        }

        return $validated;
    }

    private function throwException($label)
    {
        $attributeException = new AttributeException(
            new Phrase($label)
        );
        $attributeException->setAttributeCode(Data::PRESELECT_ATTRIBUTE);
        throw $attributeException;
    }

    /**
     * @return DataObject
     */
    public function getProductObject()
    {
        return $this->productObject;
    }

    /**
     * @param DataObject $object
     */
    public function setProductObject($object)
    {
        $this->productObject = $object;
    }
}
