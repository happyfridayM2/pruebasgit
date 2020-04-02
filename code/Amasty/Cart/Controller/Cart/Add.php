<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Amasty\Cart\Controller\Cart;

use Amasty\Cart\Model\Source\BlockType;
use Amasty\Cart\Model\Source\Option;
use Amasty\Cart\Model\Source\ConfirmPopup;
use Amasty\Cart\Model\Source\Section;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Checkout\Helper\Data as HelperData;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\DataObjectFactory as ObjectFactory;

class Add extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * @var string
     */
    protected $type = Section::CART;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Amasty\Cart\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_productHelper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $_view;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    private $imageBuilder;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurable;

    /**
     * @var null|\Magento\Catalog\Model\Product
     */
    private $quoteProduct = null;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    private $blockRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        \Amasty\Cart\Helper\Data $helper,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\LayoutInterface $layout,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        HelperData $helperData,
        Escaper $escaper,
        UrlHelper $urlHelper,
        ObjectFactory $objectFactory,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository
        );

        $this->helper = $helper;
        $this->_productHelper = $productHelper;
        $this->helperData = $helperData;
        $this->resultPageFactory = $resultPageFactory;
        $this->_view = $context->getView();
        $this->_coreRegistry = $coreRegistry;
        $this->urlHelper = $urlHelper;
        $this->catalogSession = $catalogSession;
        $this->categoryFactory = $categoryFactory;
        $this->layout = $layout;
        $this->escaper = $escaper;
        $this->cartHelper = $cartHelper;
        $this->localeResolver = $localeResolver;
        $this->objectFactory = $objectFactory;
        $this->imageBuilder = $imageBuilder;
        $this->configurable = $configurable;
        $this->filterProvider = $filterProvider;
        $this->blockRepository = $blockRepository;
    }

    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $message = __('We can\'t add this item to your shopping cart right now. Please reload the page.');
            return $this->addToCartResponse($message, 0);
        }

        $params = $this->getRequest()->getParams();
        $product = $this->_initProduct();

        /**
         * Check product availability
         */
        if (!$product) {
            $message = __('We can\'t add this item to your shopping cart right now.');
            return $this->addToCartResponse($message, 0);
        }
        $this->setProduct($product);

        try {
            if ($this->isShowOptionResponse($product, $params)) {
                return $this->showOptionsResponse($product);
            }

            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->localeResolver->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $cartModel = $this->getCartModel();
            $related = $this->getRequest()->getParam('related_product');
            $cartModel->addProduct($product, $params);
            if (!empty($related)) {
                $cartModel->addProductsByIds(explode(',', $related));
            }

            $cartModel->save();

            if ($product->getTypeId() == Configurable::TYPE_CODE && isset($params['super_attribute'])) {
                $this->setQuoteProduct($product);
                if ((bool)$this->helper->getModuleConfig('confirm_display/configurable_image')) {
                    $this->_coreRegistry->register(
                        'amasty_cart_conf_product',
                        $this->configurable->getProductByAttributes(
                            $params['super_attribute'],
                            $product
                        )
                    );
                } else {
                    $this->_coreRegistry->register('amasty_cart_conf_product', $product);
                }
            } else {
                $this->setQuoteProduct($product);
                $this->_coreRegistry->register('amasty_cart_conf_product', $product);
            }

            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->getCheckoutSession()->getNoCartRedirect(true)) {
                if (!$cartModel->getQuote()->getHasError()) {
                    $name = sprintf(
                        '<a href="%s" title="%s">%s</a>',
                        $product->getProductUrl(),
                        $product->getName(),
                        $product->getName()
                    );
                    switch ($this->type) {
                        case Section::QUOTE:
                            $message = __('%1 has been added to your quote cart', $name);
                            break;
                        case Section::CART:
                        default:
                            $message = __('%1 has been added to your cart', $name);
                    }
                    $message = '<p>' . $message . '</p>';

                    $message = $this->getProductAddedMessage($product, $message);
                    return $this->addToCartResponse($message, 1);
                } else {
                    $message = [];
                    $errors = $cartModel->getQuote()->getErrors();
                    foreach ($errors as $error) {
                        $message[] = $error->getText();
                    }

                    return $this->showMessages($message);
                }
            }
        } catch (LocalizedException $e) {
            return $this->showMessages([nl2br($this->escaper->escapeHtml($e->getMessage()))]);

        } catch (\Exception $e) {
            $message = __('We can\'t add this item to your shopping cart right now.');
            $message .= $e->getMessage();
            return $this->addToCartResponse($message, 0);
        }
    }

    /**
     * If product is composite - show popup with options
     * @param array $message
     *
     * @return mixed
     */
    protected function showMessages($message)
    {
        $product = $this->getProduct();
        if (!$product->isComposite()) {
            return $this->addToCartResponse(implode(', ', $message), 0);
        } else {
            $this->messages = $message;
            return $this->showOptionsResponse($product);
        }
    }

    protected function isShowOptionResponse($product, $params)
    {
        $requiredOptions = $product->getTypeInstance()->hasRequiredOptions($product);
        $showOptionsResponse = false;
        switch ($product->getTypeId()) {
            case 'configurable':
                $attributesCount = $product->getTypeInstance()->getConfigurableAttributes($product)->count();
                $superParamsCount = (array_key_exists('super_attribute', $params)) ?
                    count(array_filter($params['super_attribute'])) : 0;
                if (isset($params['configurable-option'])) {
                    // compatibility with Amasty_Conf product matrix
                    $matrixSelected = false;
                    foreach ($params['amconfigurable-option'] as $amConfigurableOption) {
                        $optionData = $this->helper->decode($amConfigurableOption);
                        if (isset($optionData['qty']) && $optionData['qty'] > 0) {
                            $matrixSelected = true;
                            break;
                        }
                    }
                    if (!$matrixSelected) {
                        $this->messages[] = __('Please specify the quantity of product(s).');
                        $showOptionsResponse = true;
                    }
                } elseif ($attributesCount != $superParamsCount) {
                    $showOptionsResponse = true;
                }
                break;
            case 'grouped':
                if (!array_key_exists('super_group', $params)) {
                    $showOptionsResponse = true;
                }
                break;
            case 'amgiftcard':
                if (!array_key_exists('am_giftcard_recipient_email', $params)) {
                    $showOptionsResponse = true;
                }
                break;
            case 'bundle':
                if (!array_key_exists('bundle_option', $params)) {
                    $showOptionsResponse = true;
                }
                break;
            case 'downloadable':
                if ($requiredOptions && !array_key_exists('links', $params) && !array_key_exists('options', $params)) {
                    $showOptionsResponse = true;
                }
                break;
            case 'simple':
            case 'virtual':
                // required custom options
                if ($requiredOptions && !array_key_exists('options', $params)) {
                    $showOptionsResponse = true;
                }
                break;
        }

        /* not required custom options block*/
        if (!$this->helper->isRedirectToProduct()
            && $product->getOptions()
            && $this->helper->getModuleConfig('dialog_popup/display_options') == Option::ALL_OPTIONS
            && !(array_key_exists('options', $params)
                || $this->isProductPageOrAjaxMini())
        ) {
            $showOptionsResponse = true;
        }

        $result = $this->objectFactory->create(['data' => ['show_options_response' => $showOptionsResponse]]);
        $this->_eventManager->dispatch(
            'amasty_cart_add_is_show_option_response_after',
            ['controller' => $this, 'result' => $result]
        );

        return $result->getShowOptionsResponse();
    }

    /**
     * @return bool
     */
    private function isMiniPage()
    {
        return $this->helper->getModuleConfig('dialog_popup/confirm_popup') == ConfirmPopup::MINI_PAGE;
    }

    /**
     * @return bool
     */
    private function isProductPageOrAjaxMini()
    {
        return $this->getRequest()->getParam('product_page') == 'true'
            || $this->getRequest()->getParam('requestAjaxMini') == 'true';
    }

    /**
     * Creating options popup
     * @param Product $product
     * @param string|null $submitRoute
     * @return mixed
     */
    protected function showOptionsResponse(Product $product, $submitRoute = null)
    {
        if ($this->helper->isRedirectToProduct()
            && $this->getRequest()->getParam('product_page') == "false"
        ) {
            $result['redirect'] = $product->getProductUrl();
            $resultObject = $this->objectFactory->create(['data' => ['result' => $result]]);
            $this->messageManager->addNoticeMessage(__('You need to choose options for your item.'));

            return $this->getResponse()->representJson(
                $this->helper->encode($resultObject->getResult())
            );
        }

        $this->_productHelper->initProduct($product->getEntityId(), $this);
        $page = $this->resultPageFactory->create(false, ['isIsolated' => false]);
        $page->addHandle('catalog_product_view');

        $type = $product->getTypeId();
        $page->addHandle('catalog_product_view_type_' . $type);

        $optionsHtml = $this->generateOptionsHtml($product, $page, $submitRoute);

        $isMiniPage = $this->helper->isRedirectToProduct() ? 1 : $this->isMiniPage();

        if ($isMiniPage) {
            $block = $page->getLayout()->createBlock(
                \Amasty\Cart\Block\Product\Minipage::class,
                'amasty.cart.minipage',
                [
                    'data' =>
                        [
                            'product'      => $product,
                            'optionsHtml'  => $optionsHtml,
                            'imageBuilder' => $this->imageBuilder,
                            'pageFactory'  => $this->resultPageFactory
                        ]
                ]
            );
            $message = $block->toHtml();
            $cancelTitle = __('Continue shopping');
        } else {
            $message = $optionsHtml;
            $cancelTitle = __('Cancel');
        }

        switch ($this->type) {
            case Section::QUOTE:
                $buttonTitle = __('Add to quote');
                break;
            case Section::CART:
            default:
                $buttonTitle = __('Add to cart');
        }

        $result = [
            'title'     =>  __('Set options'),
            'message'   =>  $message,
            'b2_name'   =>  $buttonTitle,
            'b1_name'   =>  $cancelTitle,
            'b2_action' =>  'self.submitFormInPopup();',
            'b1_action' =>  'self.confirmHide();',
            'align' =>  'self.confirmHide();' ,
            'is_add_to_cart' =>  '0',
            'is_minipage' => $isMiniPage ? true : false
        ];

        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $result['selected_options'] = $this->getRequest()->getParam('super_attribute', null);
        }

        $resultObject = $this->objectFactory->create(['data' => ['result' => $result]]);
        $this->_eventManager->dispatch(
            'amasty_cart_add_show_option_response_after',
            ['controller' => $this, 'product' => $product, 'result' => $resultObject]
        );

        return $this->getResponse()->representJson(
            $this->helper->encode($resultObject->getResult())
        );
    }

    /**
     * Generate html for product options
     * @param Product $product
     * @param $page
     * @param string|null $submitRoute
     *
     * @return mixed|string
     */
    protected function generateOptionsHtml(Product $product, $page, $submitRoute)
    {
        $block = $page->getLayout()->getBlock('product.info');
        if (!$block) {
            $block = $page->getLayout()->createBlock(
                \Magento\Catalog\Block\Product\View::class,
                'product.info',
                [ 'data' => [] ]
            );
        }

        $block->setProduct($product);
        if ($submitRoute) {
            $block->setData('submit_route_data', [
                'route' => $submitRoute
            ]);
        }
        $html = $block->toHtml();

        $html = str_replace(
            '"spConfig',
            '"priceHolderSelector": ".price-box[data-product-id=' . $product->getId() . ']", "spConfig',
            $html
        );

        if ($submitRoute === \Amasty\Cart\Controller\Wishlist\Cart::WISHLIST_URL) {
            $html = str_replace(
                '</form>',
                '<input name="item" type="hidden" value="'
                            . (int)$this->getRequest()->getParam('item') . '"></form>',
                $html
            );
        }

        $contentClass = 'product-options-bottom';
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $contentClass .= ' product-item';
        }

        $errors = '';
        if (count($this->messages)) {
            $errors .= '<div class="message error">' . implode(' ', $this->messages) . '</div>';
        }

        $isMiniPage = $this->helper->isRedirectToProduct() ? 1 : $this->isMiniPage();

        if ($isMiniPage) {
            $title = '';
        } else {
            $title = sprintf(
                '<a href="%s" title="%s" class="added-item">%s</a>',
                $product->getProductUrl(),
                $product->getName(),
                $product->getName()
            );
        }

        $html = '<div class="' . $contentClass . '" >' .
            $title .
            $errors .
            $html .
            '</div>';
        $html = $this->replaceHtmlElements($html, $product);

        return $html;
    }

    /**
     * @param Product $product
     * @param $message
     * @return string
     */
    protected function getProductAddedMessage(Product $product, $message)
    {
        if ($this->helper->isDisplayImageBlock()) {
            $block = $this->layout->getBlock('amasty.cart.product');
            if (!$block) {
                $block = $this->layout->createBlock(
                    \Amasty\Cart\Block\Product::class,
                    'amasty.cart.product',
                    ['data' => ['cart_type' => $this->type]]
                );
                $block->setTemplate('Amasty_Cart::dialog.phtml');
            }

            $block->setQtyHtml($this->getQtyBlockHtml());
            $block->setProduct($product);

            $message = $block->toHtml();
        } else {
            $message .= $this->getQtyBlockHtml();
        }

        //display count cart item
        if ($this->helper->isDisplayCount()) {
            $summary = $this->getCartModel()->getSummaryQty();
            if ($summary == 1) {
                $partOne = __('There is');
                $partTwo = __(' item');
            } else {
                $partOne = __('There are');
                $partTwo = __(' items');
            }

            switch ($this->type) {
                case Section::QUOTE:
                    $linkTitle = __('Quote Cart');
                    $itemCountTitle =  __(' in your quote cart.');
                    $cartUrl = $this->getQuoteCartUrl();
                    break;
                case Section::CART:
                default:
                    $linkTitle = __('View Cart');
                    $itemCountTitle =  __(' in your cart.');
                    $cartUrl = $this->cartHelper->getCartUrl();
            }
            $message .=
                "<p id='amcart-count' class='text'>".
                $partOne .
                ' <a href="'. $cartUrl .'" id="am-a-count" data-amcart="amcart-count" title="' . $linkTitle . '">'.
                $summary.  $partTwo .
                '</a> '
                . $itemCountTitle
                . "</p>";
        }

        //display sum price
        if ($this->helper->isDisplaySubtotal()) {
            $message .=
                '<p class="amcart-subtotal text">' .
                __('Cart Subtotal:') .
                ' <span class="am_price" data-amcart="amcart-price">'.
                $this->getSubtotalHtml() .
                '</span></p>';
        }

        $type = $this->helper->getModuleConfig('selling/block_type');
        if ($type && $type !== '0') {
            /* replace uenc for correct redirect*/
            $refererUrl = $this->_request->getServer('HTTP_REFERER');
            $message = $this->replaceUenc($refererUrl, $message);
        }

        return $message;
    }

    /**
     * @param $message
     * @param $result
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function makeResponse($message, $result)
    {

        switch ($this->type) {
            case Section::QUOTE:
                $cartUrl = $this->getQuoteCartUrl();
                $buttonName = __('Quote Cart');
                break;
            case Section::CART:
            default:
                $cartUrl = $this->cartHelper->getCartUrl();
                $buttonName = __('View Cart');
        }

        $result = array_merge(
            $result,
            [
                'title'     => __('Information'),
                'message'   => $message,
                'related'   => $this->getAdditionalBlockHtml(),
                'b1_name'   => __('Continue'),
                'b2_name'   => $buttonName,
                'b2_action' => 'document.location = "' . $cartUrl . '";',
                'b1_action' => 'self.confirmHide();',
                'checkout'  => '',
                'timer'     => ''
            ]
        );

        if ($this->helper->isDisplayGoToCheckout() && $this->isCartController()) {
            $goto = __('Go to Checkout');
            $result['checkout'] =
                '<a class="checkout"
                    title="' . $goto . '"
                    data-role="proceed-to-checkout"
                    href="' . $this->helper->getUrl('checkout') . '"
                    >
                    ' . $goto . '
                </a>';
        }

        $isProductView = $this->getRequest()->getParam('product_page');

        if ($isProductView == 'true' && $this->helper->getProductButton()) {
            $categoryId = $this->catalogSession->getLastVisitedCategoryId();

            if (!$categoryId && $this->getProduct()) {
                $productCategories = $this->getProduct()->getCategoryIds();

                if (count($productCategories) > 0) {
                    $categoryId = $productCategories[0];

                    if ($categoryId == $this->_storeManager->getStore()->getRootCategoryId()) {
                        if (isset($productCategories[1])) {
                            $categoryId = $productCategories[1];
                        } else {
                            $categoryId = null;
                        }
                    }
                }
            }

            if ($categoryId) {
                $category = $this->categoryFactory->create()->load($categoryId);

                if ($category) {
                    $result['b1_action'] = 'document.location = "' . $category->getUrl() . '";';
                }
            }
        }

        //add timer
        $time = $this->helper->getTime();
        if (0 < $time) {
            $result['timer'] .= '<span class="timer">' . '(' . $time . ')' . '</span>';
        }

        return $result;
    }

    /**
     * @param $message
     * @param $status
     * @param array $additionalResult
     * @return mixed
     */
    protected function addToCartResponse($message, $status, $additionalResult = [])
    {
        $result = ['is_add_to_cart' => $status];
        if (!$status) {
            $message = '<div class="message error">' . $message . '</div>';
            $result = $this->makeResponse($message, $result);
        }

        if (!$this->helper->isOpenMinicart() && $status) {
            $result = $this->makeResponse($message, $result);
        }

        $result = array_merge($result, $additionalResult);

        if ($status) {
            $result['product_sku'] = $this->getProduct()->getSku();
            $result['product_id'] = $this->getProduct()->getId();
        }

        $resultObject = $this->objectFactory->create(['data' => ['result' => $result]]);
        $this->_eventManager->dispatch(
            'amasty_cart_add_addtocart_response_after',
            ['controller' => $this, 'result' => $resultObject]
        );

        return $this->getResponse()->representJson(
            $this->helper->encode($resultObject->getResult())
        );
    }

    /**
     * @return string
     */
    protected function getAdditionalBlockHtml()
    {
        $type = $this->helper->getModuleConfig('selling/block_type');
        $html = '';
        $this->layout->createBlock(\Magento\Framework\View\Element\FormKey::class, 'formkey');
        switch ($type) {
            case BlockType::CMS_BLOCK:
                $html = $this->getCmsBlockHtml();
                break;
            case BlockType::RELATED:
            case BlockType::CROSSSELL:
                //display related products
                $html = $this->getProductsHtml($type);
                break;
        }
        $html = preg_replace(
            '@\[data-role=swatch-option-(\d+)]@',
            '#confirmBox [data-role=swatch-option-$1]',
            $html
        );

        return $html;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws LocalizedException
     */
    private function getCmsBlockHtml()
    {
        $html = '';
        if ($blockId = $this->helper->getCmsBlockId()) {
            $storeId = $this->_storeManager->getStore()->getId();
            /** @var \Magento\Cms\Model\Block $block */
            $block = $this->blockRepository->getById($blockId);
            if ($block->isActive()) {
                $html = $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter(
                    $block->getContent()
                );
            }
        }

        return $html;
    }

    /**
     * @param $type
     * @return string
     */
    private function getProductsHtml($type)
    {
        $html = '';
        $product = $this->getProduct();
        if ($product) {
            $this->_productHelper->initProduct($product->getEntityId(), $this);
            $this->layout->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => [
                    'price_render_handle' => 'catalog_product_prices',
                    'use_link_for_as_low_as' => true
                ]]
            );
            $blockName = 'Amasty\Cart\Block\Product\\';
            if ($this->helper->isTargetRuleEnabled()) {
                $blockName .= 'TargetRule\\';
            }
            $block = $this->layout->createBlock(
                $blockName . ucfirst($type),
                'amasty.cart.product_' . $type,
                ['data' => ['cart_type' => $this->type]]
            );
            $block->setProduct($product)->setTemplate("Amasty_Cart::product/list/items.phtml");
            $html = $block->toHtml();
            $refererUrl = $product->getProductUrl();
            $html = $this->replaceUenc($refererUrl, $html);
        }

        return $html;
    }

    /**
     * @return string
     */
    protected function getSubtotalHtml()
    {
        $totals = $this->getCartModel()->getQuote()->getTotals();
        $subtotal = isset($totals['subtotal']) && $totals['subtotal'] instanceof Total
            ? $totals['subtotal']->getValue()
            : 0;

        return $this->helperData->formatPrice($subtotal);
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param string $refererUrl
     * @param string $item
     * @return string mixed
     */
    private function replaceUenc($refererUrl, $item)
    {
        $currentUenc = $this->urlHelper->getEncodedUrl();
        $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);
        return str_replace($currentUenc, $newUenc, $item);
    }

    /**
     * @return string
     */
    private function getQtyBlockHtml()
    {
        $result = '';
        // if quote product not detected (example: Amasty_Conf matrix used) qty block not displayed
        if ($this->helper->isChangeQty() && $this->getQuoteProduct()) {
            // use quote getItemByProduct function for avoid getting wrong quote item in case
            // with configurable simple with different custom options
            $quoteItem = $this->getCartModel()->getQuote()->getItemByProduct($this->getQuoteProduct());
            if ($quoteItem) {
                $block = $this->layout->getBlock('amasty.cart.qty');
                if (!$block) {
                    $block = $this->layout->createBlock(
                        \Amasty\Cart\Block\Product::class,
                        'amasty.cart.qty',
                        ['data' => []]
                    );
                }
                $quoteItem = $quoteItem->getParentItem() ?: $quoteItem;

                $block->setTemplate('Amasty_Cart::qty.phtml');
                $block->setQty($quoteItem->getQty());
                $quoteItemId = $quoteItem->getData('parent_item_id') ?: $quoteItem->getData('item_id');
                $block->setQuoteItemId($quoteItemId);

                $result = $block->toHtml();
            }
        }

        return $result;
    }

    private function replaceHtmlElements($html, $product)
    {
        /* replace uenc for correct redirect*/
        $currentUenc = $this->urlHelper->getEncodedUrl();
        $refererUrl = $product->getProductUrl();
        $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);

        $html = str_replace($currentUenc, $newUenc, $html);
        $html = str_replace('"swatch-opt"', '"swatch-opt swatch-opt-' . $product->getId() . '"', $html);
        $html = str_replace('spConfig": {"attributes', 'spConfig": {"containerId":"#confirmBox", "attributes', $html);
        $html = str_replace('[data-role=swatch-options]', '#confirmBox [data-role=swatch-options]', $html);

        return $html;
    }

    /**
     * @return string
     */
    private function getQuoteCartUrl()
    {
        return $this->_url->getUrl('amasty_quote/cart');
    }

    /**
     * @return bool
     */
    private function isCartController()
    {
        return $this->type == Section::CART;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * @return CustomerCart
     */
    public function getCartModel()
    {
        return $this->cart;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     */
    private function setQuoteProduct($product)
    {
        $this->quoteProduct = $product;
    }

    /**
     * @return Product|null
     */
    private function getQuoteProduct()
    {
        return $this->quoteProduct;
    }
}
