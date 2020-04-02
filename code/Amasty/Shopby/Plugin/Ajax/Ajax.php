<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Ajax;

use Amasty\Shopby\Helper\State;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Layout\Element;

class Ajax
{
    const OSN_CONFIG = 'amasty.xnotif.config';
    const QUICKVIEW_CONFIG = 'amasty.quickview.config';

    /**
     * @var array
     */
    private $customThemes = ['fcnet/blank_julbo', 'Smartwave/porto'];

    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Url\Encoder
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Url\Decoder
     */
    protected $urlDecoder;

    /**
     * @var State
     */
    protected $stateHelper;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $design;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var \Amasty\Shopby\Model\Layer\Cms\Manager
     */
    private $cmsManager;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    public function __construct(
        \Amasty\Shopby\Helper\Data $helper,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Url\Encoder $urlEncoder,
        \Magento\Framework\Url\Decoder $urlDecoder,
        State $stateHelper,
        \Magento\Framework\View\DesignInterface $design,
        ActionFlag $actionFlag,
        \Amasty\Shopby\Model\Layer\Cms\Manager $cmsManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->helper = $helper;
        $this->resultRawFactory = $resultRawFactory;
        $this->urlEncoder = $urlEncoder;
        $this->urlDecoder = $urlDecoder;
        $this->stateHelper = $stateHelper;
        $this->design = $design;
        $this->actionFlag = $actionFlag;
        $this->cmsManager = $cmsManager;
        $this->layout = $layout;
        $this->pageConfig = $pageConfig;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eventManager = $eventManager;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    protected function isAjax(RequestInterface $request)
    {
        if (!$request instanceof Http) {
            return false;
        }
        $isAjax = $request->isXmlHttpRequest() && $request->isAjax();
        $isScroll = $request->getParam('is_scroll');
        return $this->helper->isAjaxEnabled() && $isAjax && !$isScroll;
    }

    /**
     * @param \Magento\Framework\View\Result\Page|null $page
     *
     * @return array
     */
    protected function getAjaxResponseData($page = null)
    {
        $layout = $this->layout;
        $tags = [];

        $products = $layout->getBlock('category.products');
        if (!$products) {
            $products = $layout->getBlock('search.result');
        }

        $productsCount = 0;
        $productList = null;
        if ($products) {
            $tags = $this->addXTagCache($products, $tags);
            $productList = $products->getChildBlock('product_list') ?: $products->getChildBlock('search_result_list');
            $productsCount = $productList
                ? $productList->getLoadedProductCollection()->getSize()
                : $products->getResultCount();
        }

        $navigation = $layout->getBlock('catalog.leftnav') ?: $layout->getBlock('catalogsearch.leftnav');
        if ($navigation) {
            $navigation->toHtml();
            $tags = $this->addXTagCache($navigation, $tags);
        }

        $applyButton = $layout->getBlock('amasty.shopby.applybutton.sidebar');
        $tags = $this->addXTagCache($applyButton, $tags);

        $jsInit = $layout->getBlock('amasty.shopby.jsinit');
        $tags = $this->addXTagCache($jsInit, $tags);

        $categoryProducts = $products ? $this->applyEventChanges($products->toHtml()) : '';

        $navigationTop = null;
        if (strpos($categoryProducts, 'amasty-catalog-topnav') === false) {
            $navigationTop = $layout->getBlock('amshopby.catalog.topnav');
            $tags = $this->addXTagCache($navigationTop, $tags);
        }

        $applyButtonTop = $layout->getBlock('amasty.shopby.applybutton.topnav');
        $tags = $this->addXTagCache($applyButtonTop, $tags);

        $h1 = $layout->getBlock('page.main.title');
        $tags = $this->addXTagCache($h1, $tags);

        $title = $this->pageConfig->getTitle();
        $breadcrumbs = $layout->getBlock('breadcrumbs');
        $tags = $this->addXTagCache($breadcrumbs, $tags);

        $htmlCategoryData = '';
        $children = $layout->getChildNames('category.view.container');
        foreach ($children as $child) {
            $htmlCategoryData .= $layout->renderElement($child);
            $tags = $this->addXTagCache($child, $tags);
        }

        $shopbyCollapse = $layout->getBlock('catalog.navigation.collapsing');
        $shopbyCollapseHtml = '';
        if ($shopbyCollapse) {
            $shopbyCollapseHtml = $shopbyCollapse->toHtml();
            $tags = $this->addXTagCache($shopbyCollapse, $tags);
        }

        $swatchesChoose = $layout->getBlock('catalog.navigation.swatches.choose');
        $swatchesChooseHtml = '';
        if ($swatchesChoose) {
            $swatchesChooseHtml = $swatchesChoose->toHtml();
        }

        $currentCategory = $productList && $productList->getLayer()
            ? $productList->getLayer()->getCurrentCategory()
            : false;

        $isDisplayModePage = $currentCategory && $currentCategory->getDisplayMode() == Category::DM_PAGE;

        $responseData = [
            'categoryProducts'=> $categoryProducts . $swatchesChooseHtml . $this->getAdditionalConfigs($layout),
            'navigation' =>
                ($navigation ? $navigation->toHtml() : '')
                . $shopbyCollapseHtml
                . ($applyButton ? $applyButton->toHtml() : ''),
            'navigationTop' =>
                ($navigationTop ? $navigationTop->toHtml() : '')
                . ($applyButtonTop ? $applyButtonTop->toHtml() : ''),
            'breadcrumbs' => $breadcrumbs ? $breadcrumbs->toHtml() : '',
            'h1' => $h1 ? $h1->toHtml() : '',
            'title' => $title->get(),
            'bottomCmsBlock' => $this->getBlockHtml($layout, 'amshopby.bottom'),
            'url' => $this->stateHelper->getCurrentUrl(),
            'tags' => implode(',', array_unique($tags + [\Magento\PageCache\Model\Cache\Type::CACHE_TAG])),
            'productsCount' => $productsCount,
            'js_init' => $jsInit ? $jsInit->toHtml() : '',
            'isDisplayModePage' => $isDisplayModePage,
            'currentCategoryId' => $currentCategory ? $currentCategory->getId() ?: 0 : 0,
            'currency' => $this->getBlockHtml($layout, 'currency'),
            'store' => $this->getBlockHtml($layout, 'store_language'),
            'store_switcher' => $this->getBlockHtml($layout, 'store_switcher'),
            'behaviour' => $this->getBlockHtml($layout, 'wishlist_behaviour')
        ];
        if ($layout->getBlock('category.amshopby.ajax')) {
            $responseData['newClearUrl'] = $layout->getBlock('category.amshopby.ajax')->getClearUrl();
        }

        $this->addCategoryData($htmlCategoryData, $layout, $responseData);

        try {
            $sidebarTag = $layout->getElementProperty('div.sidebar.additional', Element::CONTAINER_OPT_HTML_TAG);
            $sidebarClass = $layout->getElementProperty('div.sidebar.additional', Element::CONTAINER_OPT_HTML_CLASS);
            $sidebarAdditional = $layout->renderNonCachedElement('div.sidebar.additional');
            $responseData['sidebar_additional'] = $sidebarAdditional;
            $responseData['sidebar_additional_alias'] = $sidebarTag . '.' . str_replace(' ', '.', $sidebarClass);
        } catch (\Exception $e) {
            unset($responseData['sidebar_additional']);
        }

        $responseData = $this->removeAjaxParam($responseData);
        $responseData = $this->removeEncodedAjaxParams($responseData);

        return $responseData;
    }

    /**
     * @param $responseData
     * @param $htmlCategoryData
     * @param $layout
     */
    private function addCategoryData($htmlCategoryData, $layout, &$responseData)
    {
        if (in_array($this->design->getDesignTheme()->getCode(), $this->customThemes)) {
            $responseData['image'] = $this->getBlockHtml($layout, 'category.image');
            $responseData['description'] = $this->getBlockHtml($layout, 'category_desc_main_column');
        } else {
            // @codingStandardsIgnoreStart
            $htmlCategoryData = '<div class="category-view">' . $htmlCategoryData . '</div>';
            // @codingStandardsIgnoreEnd
            $responseData['categoryData'] = $htmlCategoryData;
        }
    }

    /**
     * @param $layout
     * @param $blockName
     * @return string
     */
    private function getBlockHtml($layout, $blockName)
    {
        return $layout->getBlock($blockName) ? $layout->getBlock($blockName)->toHtml() : '';
    }

    /**
     * @param mixed $element
     * @param array $tags
     * @return array
     */
    private function addXTagCache($element, array $tags)
    {
        if ($element instanceof IdentityInterface) {
            $tags = array_merge($tags, $element->getIdentities());
        }

        return $tags;
    }

    /**
     * @param array $responseData
     * @return array
     */
    private function removeEncodedAjaxParams(array $responseData)
    {
        $pattern = '@aHR0c(Dov|HM6)[A-Za-z0-9_-]+@u';
        array_walk($responseData, function (&$html) use ($pattern) {
            // 'aHR0cDov' and 'aHR0cHM6' are the beginning of the Base64 code for 'http:/' and 'https:'
            $res = preg_replace_callback($pattern, [$this, 'removeAjaxParamFromEncodedMatch'], $html);
            if ($res !== null) {
                $html = $res;
            }
        });

        return $responseData;
    }

    /**
     * @param array $match
     * @return string
     */
    protected function removeAjaxParamFromEncodedMatch($match)
    {
        $originalUrl = $this->urlDecoder->decode($match[0]);
        if ($originalUrl === false) {
            return $match[0];
        }
        $url = $this->removeAjaxParam($originalUrl);
        return ($originalUrl == $url) ? $match[0] : rtrim($this->urlEncoder->encode($url), ',');
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function removeAjaxParam($data)
    {
        $data = str_replace([
            '?shopbyAjax=1&amp;',
            '?shopbyAjax=1&',
        ], '?', $data);
        $data = str_replace([
            '?shopbyAjax=1',
            '&amp;shopbyAjax=1',
            '&shopbyAjax=1',
        ], '', $data);

        return $data;
    }

    /**
     * @param array $data
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    protected function prepareResponse(array $data)
    {
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        if (isset($data['tags'])) {
            $response->setHeader('X-Magento-Tags', $data['tags']);
            unset($data['tags']);
        }

        $response->setContents(json_encode($data));
        return $response;
    }

    /**
     * @param $layout
     * @return string
     */
    private function getAdditionalConfigs($layout)
    {
        $html = '';
        $html .= $this->getBlockHtml($layout, self::OSN_CONFIG);
        $html .= $this->getBlockHtml($layout, self::QUICKVIEW_CONFIG);

        return $html;
    }

    /**
     * @return \Amasty\Shopby\Model\Layer\Cms\Manager
     */
    public function getCmsManager()
    {
        return $this->cmsManager;
    }

    /**
     * @return ActionFlag
     */
    public function getActionFlag()
    {
        return $this->actionFlag;
    }

    /**
     * Compatibility with Google Page SpeedOptimizer
     * @param string $html
     *
     * @return string|mixed
     */
    protected function applyEventChanges(string $html)
    {
        $dataObject = $this->dataObjectFactory->create(
            [
                'data' => [
                    'page' => $html,
                    'pageType' => 'catalog_category_view'
                ]
            ]
        );
        $this->eventManager->dispatch('amoptimizer_process_ajax_page', ['data' => $dataObject]);
        $html = $dataObject->getData('page');

        return $html;
    }
}
