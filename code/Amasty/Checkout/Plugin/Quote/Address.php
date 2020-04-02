<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Quote;

use Amasty\Checkout\Helper\Address as AddressHelper;

/**
 * Class Address
 */
class Address
{
    /**
     * @var AddressHelper
     */
    protected $addressHelper;

    public function __construct(
        AddressHelper $addressHelper
    ) {
        $this->addressHelper = $addressHelper;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterAddData(
        \Magento\Quote\Model\Quote\Address $subject,
        $result
    ) {
        $this->addressHelper->fillEmpty($subject);

        return $result;
    }
}
