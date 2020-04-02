<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Amasty\Cart\Controller\Cart;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Post extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    private $postHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Block\Product\AbstractProduct
     */
    private $abstractProduct;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postHelper,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Block\Product\AbstractProduct $abstractProduct,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->postHelper = $postHelper;
        $this->productRepository = $productRepository;
        $this->abstractProduct = $abstractProduct;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $product = $this->_initProduct();
        if ($product) {
            $postData = $this->postHelper->getPostData(
                $this->abstractProduct->getAddToCartUrl($product),
                ['product' => $product->getEntityId()]
            );

            return $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($postData)
            );
        }

        return '';
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }

        return false;
    }
}
