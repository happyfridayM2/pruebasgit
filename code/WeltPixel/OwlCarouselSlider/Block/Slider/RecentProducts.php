<?php
namespace WeltPixel\OwlCarouselSlider\Block\Slider;

class RecentProducts extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Widget\Block\BlockInterface
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \WeltPixel\OwlCarouselSlider\Helper\Custom
     */
    protected $_helperCustom;
    /**
     * Products visibility
     * @var \Magento\Reports\Model\Event\TypeFactory
     */
    protected $_catalogProductVisibility;
    protected $_helperProducts;
    protected $_productType;
    protected $_sliderConfiguration;
    protected $_currentProduct;
    protected $_productCollectionFactory;
    protected $_reportsCollectionFactory;
    protected $_viewProductsBlock;
    protected $_urlCoder;

    const COLLECTION_TYPE = 'recently_viewed';


    /**
     * RecentProducts constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \WeltPixel\OwlCarouselSlider\Helper\Products $helperProducts
     * @param \WeltPixel\OwlCarouselSlider\Helper\Custom $helperCustom
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productsCollectionFactory
     * @param \Magento\Reports\Block\Product\Widget\Viewed\Proxy $viewedProductsBlock
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \WeltPixel\OwlCarouselSlider\Helper\Products $helperProducts,
        \WeltPixel\OwlCarouselSlider\Helper\Custom $helperCustom,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productsCollectionFactory,
        \Magento\Reports\Block\Product\Widget\Viewed\Proxy $viewedProductsBlock,
        \Magento\Framework\Encryption\UrlCoder $urlCoder,
        array $data = []
    )
    {
        $this->_coreRegistry              = $context->getRegistry();
        $this->_helperProducts            = $helperProducts;
        $this->_helperCustom              = $helperCustom;
        $this->_productCollectionFactory  = $productsCollectionFactory;
        $this->_viewProductsBlock         = $viewedProductsBlock;
        $this->_urlCoder                  = $urlCoder;

        $this->setTemplate('recent/products.phtml');

        parent::__construct($context, $data);
    }


    /**
     * Retrieve the product collection based on product type.
     *
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection()
    {
        $productCollection =  $this->_getRecentlyViewedCollection($this->_productCollectionFactory->create());
        return $productCollection;
    }

    /**
     * Retrieve the Slider settings.
     *
     * @return array
     */
    public function getSliderConfiguration()
    {
        if (is_null($this->_sliderConfiguration)) {
            $this->_sliderConfiguration = $this->_helperProducts->getSliderConfigOptions(self::COLLECTION_TYPE);
        }

        return $this->_sliderConfiguration;
    }

    /**
     * Retrieve the Slider Breakpoint settings.
     *
     * @return array
     */
    public function getBreakpointConfiguration()
    {
        return $this->_helperCustom->getBreakpointConfiguration();
    }

    /**
     * Get recently viewed slider products.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $_collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _getRecentlyViewedCollection($_collection)
    {
        $limit  = $this->_getProductLimit(self::COLLECTION_TYPE);
        $random = $this->_getRandomSort(self::COLLECTION_TYPE);

        if($limit == 0) {
            return [];
        };

        $_collection = $this->_viewProductsBlock->getItemsCollection();

        if ($random) {
            $allIds = $_collection->getAllIds();
            $candidateIds = $_collection->getAllIds();
            $randomIds = [];
            $maxKey = count($candidateIds) - 1;
            while (count($randomIds) <= count($allIds) - 1) {
                $randomKey = mt_rand(0, $maxKey);
                $randomIds[$randomKey] = $candidateIds[$randomKey];
            }

            $_collection->addIdFilter($randomIds);
        };

        if ($limit && $limit > 0 ) {
            $_collection->setPageSize($limit);
        };

        return $_collection;
    }


    /**
     * Retrieve the products limit based on type.
     *
     * @param $type
     * @return int
     */
    protected function _getProductLimit($type)
    {
        return $this->_helperProducts->getProductLimit($type);
    }

    /**
     * Retrieve the products random sort flag based on type.
     *
     * @param $type
     * @return mixed
     */
    protected function _getRandomSort($type)
    {
        return $this->_helperProducts->getRandomSort($type);
    }
    

    /**
     * Retrieve the current store id.
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return mixed
     */
    public function isHoverImageEnabled()
    {
        return $this->_helperCustom->isHoverImageEnabled();
    }

    /**
     * @param $product
     * @param array $additional
     * @return string
     */
    public function getCustomAddToCartUrl($product, $additional = [])
    {
        $referer = $this->_request->getServer('HTTP_REFERER');
        $uenc = $this->_urlCoder->encode($referer);
        $productId = $product->getEntityId();
        $params = [
            'uenc' => $uenc,
            'product' => $productId
        ];
        $url = $this->getUrl('checkout/cart/add', $params);

        return $url;
    }

}
