<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Block;

use Amasty\Cart\Model\Source\Section;
use Magento\Framework\Data\Form\FormKey;

class Product extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\Cart\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @var FormKey
     */
    protected $formKey;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Registry $registry,
        FormKey $formKey,
        \Amasty\Cart\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_helper = $helper;
        $this->formKey = $formKey;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->imageBuilder = $imageBuilder;
        $this->_registry = $registry;
    }

    /**
     * @return \Amasty\Cart\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        $confProduct = $this->_registry->registry('amasty_cart_conf_product');

        if ($confProduct) {
            $product = $confProduct;
        }

        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getMessage()
    {
        switch ($this->getCartType()) {
            case Section::QUOTE:
                $result = __('has been added to your quote cart');
                break;
            case Section::CART:
            default:
                $result =__('has been added to your cart');
        }

        return $result;
    }
}
