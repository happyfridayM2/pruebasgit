<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Model;

/**
 * Class ProductCount
 *
 * @package Amasty\ShopbyBrand\Model
 */
class ProductCount
{
    /**
     * @var null|array
     */
    private $productCount = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collection;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $brandHelper;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    private $builder;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection,
        \Amasty\ShopbyBrand\Helper\Data $brandHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $builder
    ) {
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->collection = $collection;
        $this->messageManager = $messageManager;
        $this->brandHelper = $brandHelper;
        $this->builder = $builder;
    }

    /**
     * Get brand product count
     *
     * @param int $optionId
     * @return int
     */
    public function get($optionId)
    {
        if ($this->productCount === null) {
            $attrCode = $this->brandHelper->getBrandAttributeCode();

            try {
                $this->productCount = $this->loadProductCount($attrCode);
            } catch (\Magento\Framework\Exception\StateException $e) {
                if (!$this->messageManager->hasMessages()) {
                    $this->messageManager->addErrorMessage(
                        __('Make sure that the root category for current store is anchored')
                    )->addErrorMessage(
                        __('Make sure that "%1" attribute can be used in layered navigation', $attrCode)
                    );
                }
                $this->productCount = [];
            }

        }

        return isset($this->productCount[$optionId]) ? $this->productCount[$optionId]['count'] : 0;
    }

    /**
     * @param string $attrCode
     *
     * @return array
     */
    private function loadProductCount($attrCode)
    {
        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
        $category = $this->categoryRepository->get($rootCategoryId);
        $this->collection->setSearchCriteriaBuilder($this->builder);

        return $this->collection->addAttributeToSelect($attrCode)
            ->setVisibility([2,4])
            ->addCategoryFilter($category)
            ->getFacetedData($attrCode);
    }
}
