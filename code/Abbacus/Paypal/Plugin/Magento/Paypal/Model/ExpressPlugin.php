<?php
namespace Abbacus\Paypal\Plugin\Magento\Paypal\Model;

use \Magento\Paypal\Model\Express;

/**
 * Class ExpressPlugin
 *
 * @package Abbacus\Paypal\Plugin\Magento\Paypal\Model
 */
class ExpressPlugin
{
    /**
     * Remove extension attributes from data
     *
     * @param Express $subject
     * @param \Magento\Framework\DataObject $data
     */
    public function beforeAssignData(Express $subject, \Magento\Framework\DataObject $data)
    {
        $extensionAttributeKey = \Magento\Framework\Api\ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY;
        $additionalDataKey = \Magento\Quote\Api\Data\PaymentInterface::KEY_ADDITIONAL_DATA;

        $additionalData = $data->getData($additionalDataKey);

        if (is_array($additionalData) && isset($additionalData[$extensionAttributeKey])) {
            unset($additionalData[$extensionAttributeKey]);
            $data->setData($additionalDataKey, $additionalData);
        }
    }
}