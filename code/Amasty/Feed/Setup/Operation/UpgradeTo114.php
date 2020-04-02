<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup\Operation;

/**
 * Class UpgradeTo114
 */
class UpgradeTo114
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Repository
     */
    private $attributeRepository;

    public function __construct(\Magento\Catalog\Model\Product\Attribute\Repository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function execute()
    {
        $attributesForConditions = ['status', 'quantity_and_stock_status'];
        foreach ($attributesForConditions as $code) {
            $attribute = $this->attributeRepository->get($code);
            $attribute->setIsUsedForPromoRules(true);
            $this->attributeRepository->save($attribute);
        }
    }
}
