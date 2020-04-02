<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\ConfigurableProduct\Model\ResourceModel\Attribute;

use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Framework\DB\Select;
use Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Attribute\InStockOptionSelectBuilder as NativeBuilder;
use Amasty\Conf\Helper\Data;

class InStockOptionSelectBuilder
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * InStockOptionSelectBuilder constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Disable Magento stock filter
     *
     * @param NativeBuilder $nativeSubject
     * @param \Closure $proceed
     * @param OptionSelectBuilderInterface $subject
     * @param Select $select
     * @return Select
     */
    public function aroundAfterGetSelect(
        NativeBuilder $nativeSubject,
        \Closure $proceed,
        OptionSelectBuilderInterface $subject,
        Select $select
    ) {
        if (!$this->helper->getModuleConfig('general/show_out_of_stock')) {
            $select = $proceed($subject, $select);
        }

        return $select;
    }
}
