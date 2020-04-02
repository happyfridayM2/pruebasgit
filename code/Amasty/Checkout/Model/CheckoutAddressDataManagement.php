<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class CheckoutAddressDataManagement
 */
class CheckoutAddressDataManagement
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param array $inputArray
     *
     * @return array
     */
    public function prepareAddressData($inputArray)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.3', '<')) {
            return $inputArray;
        } else {
            return $this->getCustomAttributesValues($inputArray);
        }
    }

    /**
     * @param array $customAttributes
     *
     * @return array
     */
    private function getCustomAttributesValues($customAttributes)
    {
        $data = [];

        foreach ($customAttributes as $customAttribute) {
            $data[$customAttribute[AttributeMetadataInterface::ATTRIBUTE_CODE]] = $customAttribute['value'];
        }

        return $data;
    }
}
