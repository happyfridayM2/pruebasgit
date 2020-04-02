<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\Mail\Template;

/**
 * Class TransportBuilder
 */
class TransportBuilder
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Framework\Mail\Template\TransportBuilder $subject
     * @param \Closure $proceed
     * @param string|array $from
     *
     * @return \Magento\Framework\Mail\Template\TransportBuilder
     */
    public function aroundSetFrom(
        \Magento\Framework\Mail\Template\TransportBuilder $subject,
        \Closure $proceed,
        $from
    ) {
        //fix issue with sender store value
        $storeId = $this->getStoreId();
        if ($storeId && method_exists($subject, 'setFromByScope')) {
            $result = $subject->setFromByScope($from, $storeId);
        } else {
            $result = $proceed($from);
        }

        return $result;
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        return $this->registry->registry('amasty_store_id');
    }
}
