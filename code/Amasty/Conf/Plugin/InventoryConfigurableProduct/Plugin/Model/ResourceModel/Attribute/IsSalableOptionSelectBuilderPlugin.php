<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\InventoryConfigurableProduct\Plugin\Model\ResourceModel\Attribute;

// phpcs:ignore
use Magento\InventoryConfigurableProduct\Plugin\Model\ResourceModel\Attribute\IsSalableOptionSelectBuilder;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Framework\DB\Select;
use Amasty\Conf\Helper\Data;

class IsSalableOptionSelectBuilderPlugin
{
    /**
     * @var Data
     */
    private $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param IsSalableOptionSelectBuilder $subject
     * @param \Closure $proceed
     * @param OptionSelectBuilderInterface $origSubject
     * @param Select $select
     *
     * @return Select
     */
    public function aroundAfterGetSelect(
        IsSalableOptionSelectBuilder $subject,
        \Closure $proceed,
        OptionSelectBuilderInterface $origSubject,
        Select $select
    ) {
        if (!$this->helper->getModuleConfig('general/show_out_of_stock')) {
            $select = $proceed($origSubject, $select);
        }

        return $select;
    }
}
