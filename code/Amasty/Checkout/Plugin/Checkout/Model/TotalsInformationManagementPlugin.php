<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Checkout\Model;

class TotalsInformationManagementPlugin
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Amasty\Checkout\Model\Config
     */
    private $checkoutConfig;

    /**
     * @var int
     */
    private $currentCartId;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Amasty\Checkout\Model\Config $checkoutConfig
    ) {
        $this->cartRepository = $cartRepository;
        $this->checkoutConfig = $checkoutConfig;
    }

    /**
     * @param \Magento\Checkout\Model\TotalsInformationManagement $subject
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     */
    public function beforeCalculate(
        \Magento\Checkout\Model\TotalsInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    ) {
        $this->currentCartId = $cartId;
    }

    /**
     * Save calculated totals
     *
     * @param \Magento\Checkout\Model\TotalsInformationManagement $subject
     * @param \Magento\Quote\Api\Data\TotalsInterface $result
     *
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function afterCalculate(\Magento\Checkout\Model\TotalsInformationManagement $subject, $result)
    {
        if ($this->currentCartId && $this->checkoutConfig->isEnabled()) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->cartRepository->get($this->currentCartId);
            if ($quote->getId()) {
                // cartRepository->save don't save totals
                $quote->save();
            }
        }

        return $result;
    }
}
