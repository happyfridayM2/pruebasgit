<?php
/**
 * @category  Trustedshops
 * @package   Trustedshops\Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */

namespace Trustedshops\Trustedshops\Model\System;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Framework\Option\ArrayInterface;

class Attributes implements ArrayInterface
{
    /**
     * @var Collection
     */
    protected $attributeCollection;

    /**
     * @param Collection $attributeCollection
     * @internal param ObjectManagerInterface $objectManager
     */
    public function __construct(Collection $attributeCollection)
    {
        $this->attributeCollection = $attributeCollection;
    }

    public function toOptionArray()
    {
        return $this->getAvailableAttributes();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->getAvailableAttributes(true);
    }

    /**
     * @param bool $asArray
     * @return array
     */
    protected function getAvailableAttributes($asArray = false)
    {
        $availableAttributes = $this->attributeCollection
            ->setOrder('frontend_label', 'asc')
            ->load();

        $attributes = [];
        if ($asArray) {
            $attributes[0] = __('-- Please Select --');
        } else {
            $attributes[] = [
                'value' => 0,
                'label' => __('-- Please Select --')
            ];
        }

        foreach ($availableAttributes as $_attribute) {
            $_attributeCode = $_attribute->getAttributeCode();
            $label = $_attribute->getFrontendLabel();
            if (empty($label)) {
                continue;
            }
            if ($asArray) {
                $attributes[$_attributeCode] = $_attribute->getStoreLabel();
            } else {
                $attributes[] = [
                    'value' => $_attributeCode,
                    'label' => $_attribute->getStoreLabel()
                ];
            }
        }

        return $attributes;
    }
}
